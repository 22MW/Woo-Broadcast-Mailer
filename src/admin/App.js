import { Card, CardBody } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SourceSelector from './components/SourceSelector';
import DependentSelector from './components/DependentSelector';
import AudienceBuilder from './components/AudienceBuilder';
import GlobalAudienceList from './components/GlobalAudienceList';
import ManualEmailsInput from './components/ManualEmailsInput';

function mapOptionsFromSelect(selectId) {
  const element = document.getElementById(selectId);
  if (!element) {
    return [];
  }

  return Array.from(element.options).map((option) => ({
    label: option.text,
    value: option.value,
    disabled: option.disabled,
  }));
}

function getCurrentValue(selectId) {
  const element = document.getElementById(selectId);
  return element ? element.value : '';
}

function syncSelectValue(selectId, value) {
  const element = document.getElementById(selectId);
  if (!element) {
    return;
  }

  element.value = value;
  element.dispatchEvent(new Event('change', { bubbles: true }));
}

function getSourceLabel(source, sourceOptions) {
  const sourceMatch = sourceOptions.find((option) => option.value === source);
  return sourceMatch ? sourceMatch.label : source;
}

function getSelectorForSource(source, values) {
  if (source === 'role') {
    return values.roleValue;
  }
  if (source === 'mailmint') {
    return values.mailmintValue;
  }
  return values.productValue;
}

function getSelectorLabel(source, values, optionsMap) {
  const selectorValue = getSelectorForSource(source, values);
  if (!selectorValue) {
    return '';
  }

  const options = optionsMap[source] || [];
  const match = options.find((option) => option.value === selectorValue);
  return match ? match.label : selectorValue;
}

function parseEmails(input) {
  const tokens = input.split(/[\n,;]+/).map((item) => item.trim().toLowerCase());
  return tokens.filter(Boolean);
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

async function postAjax(params) {
  const response = await fetch(window.ajaxurl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: params.toString(),
  });

  return response.json();
}

async function fetchRecipientCount(source, productId, role, mailmintListId, nonce) {
  if (!window.ajaxurl) {
    return 0;
  }

  const params = new URLSearchParams();
  params.append('action', 'pbm_count_recipients');
  params.append('source', source || 'product');
  params.append('product_id', productId || '');
  params.append('role', role || '');
  params.append('mailmint_list_id', mailmintListId || '');
  params.append('nonce', nonce || '');

  const data = await postAjax(params);
  if (!data || !data.success || !data.data) {
    return 0;
  }

  return Number(data.data.total || 0);
}

async function fetchAudienceItemEmails(source, selectorValue, nonce) {
  if (!window.ajaxurl) {
    return [];
  }

  const params = new URLSearchParams();
  params.append('action', 'pbm_resolve_audience_item');
  params.append('source', source || 'product');
  params.append('selector_value', selectorValue || '');
  params.append('nonce', nonce || '');

  const data = await postAjax(params);
  if (!data || !data.success || !data.data || !Array.isArray(data.data.emails)) {
    return [];
  }

  return data.data.emails;
}

export default function App() {
  const [source, setSource] = useState(getCurrentValue('pbm_recipient_source') || 'product');
  const [productValue, setProductValue] = useState(getCurrentValue('pbm_product_id'));
  const [roleValue, setRoleValue] = useState(getCurrentValue('pbm_user_role'));
  const [mailmintValue, setMailmintValue] = useState(getCurrentValue('pbm_mailmint_list'));
  const [globalAudience, setGlobalAudience] = useState([]);
  const [manualEmails, setManualEmails] = useState([]);
  const [message, setMessage] = useState(null);
  const [currentCount, setCurrentCount] = useState(0);
  const [itemDetails, setItemDetails] = useState({});

  const sourceOptions = useMemo(() => mapOptionsFromSelect('pbm_recipient_source'), []);
  const productOptions = useMemo(() => mapOptionsFromSelect('pbm_product_id'), []);
  const roleOptions = useMemo(() => mapOptionsFromSelect('pbm_user_role'), []);
  const mailmintOptions = useMemo(() => mapOptionsFromSelect('pbm_mailmint_list'), []);

  const optionsMap = useMemo(
    () => ({
      product: productOptions,
      role: roleOptions,
      mailmint: mailmintOptions,
    }),
    [mailmintOptions, productOptions, roleOptions]
  );

  useEffect(() => {
    syncSelectValue('pbm_recipient_source', source);
  }, [source]);

  const activeSelector = useMemo(() => {
    if (source === 'role') {
      return {
        value: roleValue,
        options: roleOptions,
        onChange: (value) => {
          setRoleValue(value);
          syncSelectValue('pbm_user_role', value);
        },
      };
    }

    if (source === 'mailmint') {
      return {
        value: mailmintValue,
        options: mailmintOptions,
        onChange: (value) => {
          setMailmintValue(value);
          syncSelectValue('pbm_mailmint_list', value);
        },
      };
    }

    return {
      value: productValue,
      options: productOptions,
      onChange: (value) => {
        setProductValue(value);
        syncSelectValue('pbm_product_id', value);
      },
    };
  }, [mailmintOptions, mailmintValue, productOptions, productValue, roleOptions, roleValue, source]);

  const currentSelectorValue = getSelectorForSource(source, {
    productValue,
    roleValue,
    mailmintValue,
  });

  const currentSelectorLabel = getSelectorLabel(
    source,
    { productValue, roleValue, mailmintValue },
    optionsMap
  );

  useEffect(() => {
    let isMounted = true;

    const run = async () => {
      if (!currentSelectorValue) {
        setCurrentCount(0);
        return;
      }

      const nonceField = document.getElementById('pbm_nonce');
      const nonce = nonceField ? nonceField.value : '';

      const total = await fetchRecipientCount(source, productValue, roleValue, mailmintValue, nonce);
      if (isMounted) {
        setCurrentCount(total);
      }
    };

    run();

    return () => {
      isMounted = false;
    };
  }, [currentSelectorValue, mailmintValue, productValue, roleValue, source]);

  const addToGlobalAudience = async () => {
    if (!currentSelectorValue) {
      setMessage({ type: 'warning', text: __('Debes seleccionar un elemento antes de añadir.', 'wc-pbm') });
      return;
    }

    const key = `${source}:${currentSelectorValue}`;
    const duplicate = globalAudience.some((item) => item.key === key);

    if (duplicate) {
      setMessage({ type: 'warning', text: __('Ese elemento ya está en la lista global.', 'wc-pbm') });
      return;
    }

    const newItem = {
      key,
      source,
      sourceLabel: getSourceLabel(source, sourceOptions),
      selectorValue: currentSelectorValue,
      selectorLabel: currentSelectorLabel,
      count: currentCount,
    };

    setGlobalAudience((prev) => [...prev, newItem]);
    setMessage({ type: 'success', text: __('Elemento añadido a la lista global.', 'wc-pbm') });

    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const emails = await fetchAudienceItemEmails(source, currentSelectorValue, nonce);

    setItemDetails((prev) => ({
      ...prev,
      [key]: {
        emails,
        count: emails.length,
      },
    }));
  };

  const addManualEmails = (rawInput) => {
    const parsed = parseEmails(rawInput);
    if (parsed.length === 0) {
      setMessage({ type: 'warning', text: __('No hay emails para añadir.', 'wc-pbm') });
      return;
    }

    const validEmails = parsed.filter(isValidEmail);
    const invalidCount = parsed.length - validEmails.length;

    if (validEmails.length === 0) {
      setMessage({ type: 'warning', text: __('Todos los emails son inválidos.', 'wc-pbm') });
      return;
    }

    const uniqueToAdd = [];
    const existingManual = new Set(manualEmails);

    validEmails.forEach((email) => {
      if (!existingManual.has(email)) {
        existingManual.add(email);
        uniqueToAdd.push(email);
      }
    });

    if (uniqueToAdd.length === 0) {
      setMessage({ type: 'warning', text: __('Los emails manuales ya estaban añadidos.', 'wc-pbm') });
      return;
    }

    setManualEmails((prev) => [...prev, ...uniqueToAdd]);

    if (invalidCount > 0) {
      setMessage({ type: 'warning', text: __('Algunos emails eran inválidos y no se añadieron.', 'wc-pbm') });
      return;
    }

    setMessage({ type: 'success', text: __('Emails manuales añadidos.', 'wc-pbm') });
  };

  const removeFromGlobalAudience = (key) => {
    if ('manual:group' === key) {
      setManualEmails([]);
      setMessage(null);
      return;
    }

    setGlobalAudience((prev) => prev.filter((item) => item.key !== key));
    setItemDetails((prev) => {
      const next = { ...prev };
      delete next[key];
      return next;
    });
    setMessage(null);
  };

  const clearGlobalAudience = () => {
    setGlobalAudience([]);
    setManualEmails([]);
    setItemDetails({});
    setMessage(null);
  };

  const listItems = useMemo(() => {
    const items = globalAudience.map((item) => {
      const details = itemDetails[item.key] || { emails: [], count: item.count || 0 };
      return {
        ...item,
        count: details.count,
        emails: details.emails,
      };
    });

    if (manualEmails.length > 0) {
      items.push({
        key: 'manual:group',
        source: 'manual',
        sourceLabel: __('Manual', 'wc-pbm'),
        selectorValue: 'manual-group',
        selectorLabel: __('Bloque manual', 'wc-pbm'),
        count: manualEmails.length,
        emails: manualEmails,
      });
    }

    return items;
  }, [globalAudience, itemDetails, manualEmails]);

  const summary = useMemo(() => {
    const allEmails = [];
    listItems.forEach((item) => {
      if (Array.isArray(item.emails)) {
        allEmails.push(...item.emails.map((email) => String(email).toLowerCase()));
      }
    });

    const gross = allEmails.length;
    const unique = new Set(allEmails).size;
    const duplicates = Math.max(0, gross - unique);

    return { gross, unique, duplicates };
  }, [listItems]);

  return (
    <Card className="pbm-react-shell">
      <CardBody>
        <SourceSelector sources={sourceOptions} source={source} onChange={setSource} />
        <DependentSelector
          source={source}
          options={activeSelector.options}
          value={activeSelector.value}
          onChange={activeSelector.onChange}
          currentCount={currentCount}
        />
        <AudienceBuilder
          canAdd={Boolean(currentSelectorValue)}
          selectedLabel={currentSelectorLabel}
          onAdd={addToGlobalAudience}
          message={message}
        />
        <ManualEmailsInput onAddManualEmails={addManualEmails} />
        <GlobalAudienceList
          items={listItems}
          onRemove={removeFromGlobalAudience}
          onClear={clearGlobalAudience}
          summary={summary}
        />
      </CardBody>
    </Card>
  );
}
