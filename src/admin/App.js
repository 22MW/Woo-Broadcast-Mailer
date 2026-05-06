import { Card, CardBody } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SourceSelector from './components/SourceSelector';
import DependentSelector from './components/DependentSelector';
import AudienceBuilder from './components/AudienceBuilder';
import GlobalAudienceList from './components/GlobalAudienceList';
import ManualEmailsInput from './components/ManualEmailsInput';
import ScheduledLogsPanel from './components/ScheduledLogsPanel';

function mapOptionsFromSelect(selectId) {
  const element = document.getElementById(selectId);
  if (!element) {
    return [];
  }

  return Array.from(element.options)
    .filter((option) => option.value)
    .map((option) => ({
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

async function fetchRecipientCount(source, selectorValue, nonce) {
  if (!window.ajaxurl || !selectorValue) {
    return 0;
  }

  const params = new URLSearchParams();
  params.append('action', 'pbm_count_recipients');
  params.append('source', source);
  params.append('product_id', source === 'product' ? selectorValue : '');
  params.append('role', source === 'role' ? selectorValue : '');
  params.append('mailmint_list_id', source === 'mailmint' ? selectorValue : '');
  params.append('nonce', nonce || '');

  const data = await postAjax(params);
  if (!data || !data.success || !data.data) {
    return 0;
  }

  return Number(data.data.total || 0);
}

async function fetchAudienceItemEmails(source, selectorValue, nonce) {
  if (!window.ajaxurl || !selectorValue) {
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

async function fetchSelectorItems(source, query, nonce) {
  const params = new URLSearchParams();
  params.append('action', 'pbm_search_selectors');
  params.append('source', source);
  params.append('q', query || '');
  params.append('nonce', nonce || '');

  const data = await postAjax(params);
  if (!data || !data.success || !data.data || !Array.isArray(data.data.items)) {
    return [];
  }

  return data.data.items;
}

export default function App() {
  const [source, setSource] = useState(getCurrentValue('pbm_recipient_source') || 'product');
  const [globalAudience, setGlobalAudience] = useState([]);
  const [manualEmails, setManualEmails] = useState([]);
  const [message, setMessage] = useState(null);
  const [itemDetails, setItemDetails] = useState({});
  const [countByKey, setCountByKey] = useState({});

  const [searchTerm, setSearchTerm] = useState('');
  const [topItemsBySource, setTopItemsBySource] = useState({ product: [], role: [], mailmint: [] });
  const [searchResults, setSearchResults] = useState([]);
  const [selectedBySource, setSelectedBySource] = useState({ product: [], role: [], mailmint: [] });
  const [labelsBySource, setLabelsBySource] = useState({ product: {}, role: {}, mailmint: {} });

  const sourceOptions = useMemo(() => mapOptionsFromSelect('pbm_recipient_source'), []);

  useEffect(() => {
    syncSelectValue('pbm_recipient_source', source);
    setSearchTerm('');
    setSearchResults([]);
  }, [source]);

  useEffect(() => {
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';

    const run = async () => {
      const items = await fetchSelectorItems(source, '', nonce);
      setTopItemsBySource((prev) => ({ ...prev, [source]: items }));

      setLabelsBySource((prev) => {
        const next = { ...prev };
        const mapped = { ...next[source] };
        items.forEach((item) => {
          mapped[item.value] = item.label;
        });
        next[source] = mapped;
        return next;
      });
    };

    run();
  }, [source]);

  useEffect(() => {
    if (searchTerm.length < 3) {
      setSearchResults([]);
      return;
    }

    let alive = true;
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';

    const timer = setTimeout(async () => {
      const items = await fetchSelectorItems(source, searchTerm, nonce);
      if (!alive) {
        return;
      }

      setSearchResults(items);
      setLabelsBySource((prev) => {
        const next = { ...prev };
        const mapped = { ...next[source] };
        items.forEach((item) => {
          mapped[item.value] = item.label;
        });
        next[source] = mapped;
        return next;
      });
    }, 250);

    return () => {
      alive = false;
      clearTimeout(timer);
    };
  }, [searchTerm, source]);

  const toggleSelection = async (item) => {
    setSelectedBySource((prev) => {
      const selected = prev[source] || [];
      const exists = selected.includes(item.value);
      const nextSelected = exists ? selected.filter((value) => value !== item.value) : [...selected, item.value];

      const next = { ...prev, [source]: nextSelected };
      const primary = nextSelected[0] || '';
      if (source === 'product') {
        syncSelectValue('pbm_product_id', primary);
      } else if (source === 'role') {
        syncSelectValue('pbm_user_role', primary);
      } else if (source === 'mailmint') {
        syncSelectValue('pbm_mailmint_list', primary);
      }

      return next;
    });

    const key = `${source}:${item.value}`;
    if (typeof countByKey[key] === 'undefined') {
      const nonceField = document.getElementById('pbm_nonce');
      const nonce = nonceField ? nonceField.value : '';
      const count = await fetchRecipientCount(source, item.value, nonce);
      setCountByKey((prev) => ({ ...prev, [key]: count }));
    }
  };

  const selectedValues = selectedBySource[source] || [];
  const selectedLabel = selectedValues.length
    ? __('Seleccionados: ', 'wc-pbm') + selectedValues.length
    : '';

  const addToGlobalAudience = async () => {
    if (selectedValues.length === 0) {
      setMessage({ type: 'warning', text: __('Debes seleccionar al menos un elemento.', 'wc-pbm') });
      return;
    }

    const sourceLabel = sourceOptions.find((option) => option.value === source)?.label || source;
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';

    for (const selectorValue of selectedValues) {
      const key = `${source}:${selectorValue}`;
      const duplicate = globalAudience.some((item) => item.key === key);
      if (duplicate) {
        continue;
      }

      const label = labelsBySource[source]?.[selectorValue] || selectorValue;
      const count = typeof countByKey[key] === 'number'
        ? countByKey[key]
        : await fetchRecipientCount(source, selectorValue, nonce);

      const newItem = {
        key,
        source,
        sourceLabel,
        selectorValue,
        selectorLabel: label,
        count,
      };

      setGlobalAudience((prev) => [...prev, newItem]);

      const emails = await fetchAudienceItemEmails(source, selectorValue, nonce);
      setItemDetails((prev) => ({
        ...prev,
        [key]: {
          emails,
          count: emails.length,
        },
      }));
      setCountByKey((prev) => ({ ...prev, [key]: emails.length }));
    }

    setMessage({ type: 'success', text: __('Elementos añadidos a la lista global.', 'wc-pbm') });
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

  useEffect(() => {
    const audienceInput = document.getElementById('pbm_audience_items');
    const manualInput = document.getElementById('pbm_manual_emails');

    if (audienceInput) {
      const payload = globalAudience.map((item) => ({
        source: item.source,
        selectorValue: item.selectorValue,
      }));
      audienceInput.value = JSON.stringify(payload);
    }

    if (manualInput) {
      manualInput.value = JSON.stringify(manualEmails);
    }
  }, [globalAudience, manualEmails]);

  return (
    <Card className="pbm-react-shell">
      <CardBody>
        <SourceSelector sources={sourceOptions} source={source} onChange={setSource} />
        <DependentSelector
          source={source}
          topItems={topItemsBySource[source] || []}
          searchTerm={searchTerm}
          onSearchTermChange={setSearchTerm}
          searchResults={searchResults}
          selectedValues={selectedValues}
          onToggleSelection={toggleSelection}
          countByKey={countByKey}
        />
        <AudienceBuilder
          canAdd={selectedValues.length > 0}
          selectedLabel={selectedLabel}
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
        <ScheduledLogsPanel />
      </CardBody>
    </Card>
  );
}
