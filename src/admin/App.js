import { Button, Card, CardBody, CheckboxControl, TextControl } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
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
}

function parseEmails(input) {
  const tokens = input.split(/[\n,;]+/).map((item) => item.trim().toLowerCase());
  return tokens.filter(Boolean);
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function getClassicEditorMessage() {
  if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get('pbm_message')) {
    window.tinyMCE.get('pbm_message').save();
  }

  const textarea = document.getElementById('pbm_message');
  return textarea ? String(textarea.value || '') : '';
}

function formatEstimatedDuration(totalMinutes) {
  const minutes = Math.max(0, Math.round(totalMinutes));
  const hours = Math.floor(minutes / 60);
  const remainder = minutes % 60;
  if (hours < 1) {
    return `${minutes} min`;
  }
  if (remainder === 0) {
    return `${hours} h`;
  }
  return `${hours} h ${remainder} min`;
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
  const messageHostRef = useRef(null);
  const [source, setSource] = useState(getCurrentValue('pbm_recipient_source') || 'product');
  const [globalAudience, setGlobalAudience] = useState([]);
  const [manualEmails, setManualEmails] = useState([]);
  const [message, setMessage] = useState(null);
  const [itemDetails, setItemDetails] = useState({});
  const [countByKey, setCountByKey] = useState({});
  const [subject, setSubject] = useState('');
  const [batchSize, setBatchSize] = useState('30');
  const [emailsPerHour, setEmailsPerHour] = useState('200');
  const [scheduleEnabled, setScheduleEnabled] = useState(false);
  const [scheduledDatetime, setScheduledDatetime] = useState('');
  const [previewLoading, setPreviewLoading] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [sending, setSending] = useState(false);

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

  const deliveryEstimate = useMemo(() => {
    const unique = Math.max(0, Number(summary.unique || 0));
    const batch = Math.max(1, parseInt(batchSize || '0', 10) || 1);
    const perHour = Math.max(1, parseInt(emailsPerHour || '0', 10) || 1);
    const batches = unique > 0 ? Math.ceil(unique / batch) : 0;
    const intervalMinutes = Math.ceil((batch / perHour) * 60);
    const totalWindowMinutes = batches > 0 ? batches * intervalMinutes : 0;

    return {
      unique,
      batch,
      perHour,
      batches,
      intervalMinutes,
      totalWindowMinutes,
    };
  }, [summary.unique, batchSize, emailsPerHour]);

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

  useEffect(() => {
    const host = messageHostRef.current;
    const row = document.getElementById('pbm-message-row-legacy');
    if (!host || !row) {
      return;
    }

    const editorWrap = row.querySelector('#wp-pbm_message-wrap');
    const description = row.querySelector('.description');
    if (editorWrap && !host.contains(editorWrap)) {
      host.appendChild(editorWrap);
    }
    if (description && !host.contains(description)) {
      host.appendChild(description);
    }

    row.style.display = 'none';
  }, []);

  const previewAudience = async () => {
    if (listItems.length === 0) {
      window.alert(__('Añade destinatarios antes de previsualizar.', 'wc-pbm'));
      return;
    }

    setPreviewLoading(true);
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_preview_recipients');
    params.append('source', source);
    params.append('product_id', '');
    params.append('role', '');
    params.append('mailmint_list_id', '');
    params.append('audience_items', JSON.stringify(globalAudience.map((item) => ({ source: item.source, selectorValue: item.selectorValue }))));
    params.append('manual_emails', JSON.stringify(manualEmails));
    params.append('nonce', nonce);

    const data = await postAjax(params);
    setPreviewLoading(false);

    if (!data || !data.success || !data.data) {
      window.alert(data?.data?.message || __('Error al obtener destinatarios', 'wc-pbm'));
      return;
    }

    setPreviewData(data.data);
  };

  const sendBroadcast = async () => {
    if (listItems.length === 0) {
      window.alert(__('Añade destinatarios antes de enviar.', 'wc-pbm'));
      return;
    }
    if (!previewData || Number(previewData.total || 0) < 1) {
      window.alert(__('Primero debes hacer una vista previa.', 'wc-pbm'));
      return;
    }

    const messageContent = getClassicEditorMessage();
    if (!subject.trim() || !messageContent.trim()) {
      window.alert(__('Asunto y mensaje son obligatorios.', 'wc-pbm'));
      return;
    }

    if (scheduleEnabled && !scheduledDatetime) {
      window.alert(__('Debes indicar fecha y hora para programar.', 'wc-pbm'));
      return;
    }

    if (!window.confirm(__('¿Confirmas el envío a la audiencia seleccionada?', 'wc-pbm'))) {
      return;
    }

    setSending(true);
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_send_broadcast');
    params.append('source', source);
    params.append('product_id', '');
    params.append('role', '');
    params.append('mailmint_list_id', '');
    params.append('audience_items', JSON.stringify(globalAudience.map((item) => ({ source: item.source, selectorValue: item.selectorValue }))));
    params.append('manual_emails', JSON.stringify(manualEmails));
    params.append('subject', subject);
    params.append('message', messageContent);
    params.append('batch_size', String(parseInt(batchSize || '30', 10) || 30));
    params.append('emails_per_hour', String(parseInt(emailsPerHour || '200', 10) || 200));
    params.append('schedule_enabled', scheduleEnabled ? '1' : '0');
    params.append('scheduled_datetime', scheduledDatetime);
    params.append('nonce', nonce);

    const data = await postAjax(params);
    setSending(false);

    if (!data || !data.success) {
      window.alert(data?.data?.message || __('Error al programar el envío', 'wc-pbm'));
      return;
    }

    window.alert(data.data.message || __('Envío creado correctamente', 'wc-pbm'));
    window.location.reload();
  };

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
        <div className="pbm-react-preview-panel">
          <Button variant="secondary" onClick={previewAudience} disabled={previewLoading || listItems.length === 0}>
            {previewLoading ? __('Cargando…', 'wc-pbm') : __('Vista Previa de Destinatarios', 'wc-pbm')}
          </Button>
          {previewData && (
            <div className="pbm-react-preview-box">
              <p><strong>{__('Total de destinatarios únicos:', 'wc-pbm')}</strong> {previewData.total || 0}</p>
              <p><strong>{__('Fuente:', 'wc-pbm')}</strong> {__('Lista global combinada', 'wc-pbm')}</p>
              <div className="pbm-react-preview-emails">{(previewData.emails || []).join(', ')}</div>
            </div>
          )}
        </div>
        <div className="pbm-react-send-config">
          <TextControl label={__('Asunto', 'wc-pbm')} value={subject} onChange={setSubject} />
          <div className="pbm-react-classic-editor">
            <label htmlFor="pbm_message">{__('Mensaje', 'wc-pbm')}</label>
            <div ref={messageHostRef} />
          </div>
          <div className="pbm-react-send-grid">
            <TextControl label={__('Tamaño de lote', 'wc-pbm')} type="number" min={10} max={100} value={batchSize} onChange={setBatchSize} />
            <TextControl label={__('Emails por hora', 'wc-pbm')} type="number" min={10} max={1000} value={emailsPerHour} onChange={setEmailsPerHour} />
            <CheckboxControl label={__('Programar envío', 'wc-pbm')} checked={scheduleEnabled} onChange={setScheduleEnabled} />
            <TextControl
              label={__('Fecha y hora de envío', 'wc-pbm')}
              type="datetime-local"
              value={scheduledDatetime}
              onChange={setScheduledDatetime}
              style={{ visibility: scheduleEnabled ? 'visible' : 'hidden' }}
            />
          </div>
          <div className="pbm-react-estimate">
            <strong>{__('Resumen estimado:', 'wc-pbm')}</strong>{' '}
            {`${deliveryEstimate.unique} ${__('únicos', 'wc-pbm')} · ${deliveryEstimate.batches} ${__('lotes', 'wc-pbm')} · ${deliveryEstimate.intervalMinutes} ${__('min entre lotes', 'wc-pbm')} · ${formatEstimatedDuration(deliveryEstimate.totalWindowMinutes)}`}
          </div>
          <div className="pbm-react-send-actions">
            <Button variant="primary" onClick={sendBroadcast} disabled={sending || listItems.length === 0}>
              {sending ? __('Programando envíos...', 'wc-pbm') : __('Enviar Emails', 'wc-pbm')}
            </Button>
          </div>
        </div>
        <ScheduledLogsPanel />
      </CardBody>
    </Card>
  );
}
