import { Button, Card, CardBody, CheckboxControl, RadioControl, TextControl } from '@wordpress/components';
import { createPortal, useCallback, useEffect, useMemo, useRef, useState } from '@wordpress/element';
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

function setClassicEditorMessage(content) {
  if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get('pbm_message')) {
    window.tinyMCE.get('pbm_message').setContent(content || '');
  }

  const textarea = document.getElementById('pbm_message');
  if (textarea) {
    textarea.value = content || '';
  }
}

function insertIntoClassicEditor(html) {
  if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get('pbm_message')) {
    window.tinyMCE.get('pbm_message').execCommand('mceInsertContent', false, html);
    return;
  }

  setClassicEditorMessage(`${getClassicEditorMessage()}${html}`);
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

function buildPreviewSignature({ globalAudience, manualEmails, batchSize, emailsPerHour }) {
  const audience = globalAudience.map((item) => ({
    source: item.source,
    selectorValue: item.selectorValue,
  }));

  return JSON.stringify({
    audience,
    manualEmails,
    batchSize: String(parseInt(batchSize || '30', 10) || 30),
    emailsPerHour: String(parseInt(emailsPerHour || '200', 10) || 200),
  });
}

function uniqueEmails(emails) {
  return Array.from(new Set((emails || []).map((email) => String(email).trim().toLowerCase()).filter(isValidEmail)));
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

function ToastViewport({ toasts }) {
  if (!toasts.length) {
    return null;
  }

  return (
    <div className="pbm-admin-toasts" aria-live="polite" aria-atomic="true">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          className={`pbm-admin-toast pbm-admin-toast-${toast.type || 'success'}${toast.hiding ? ' is-hiding' : ''}`}
          role="alert"
        >
          {toast.text}
        </div>
      ))}
    </div>
  );
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
  params.append('broadcast_list_id', source === 'broadcast_list' ? selectorValue : '');
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
  const [toasts, setToasts] = useState([]);
  const [itemDetails, setItemDetails] = useState({});
  const [countByKey, setCountByKey] = useState({});
  const [subject, setSubject] = useState('');
  const [batchSize, setBatchSize] = useState('30');
  const [emailsPerHour, setEmailsPerHour] = useState('200');
  const [scheduleEnabled, setScheduleEnabled] = useState(false);
  const [audienceMode, setAudienceMode] = useState('fixed');
  const [plainBody, setPlainBody] = useState(false);
  const [scheduledDatetime, setScheduledDatetime] = useState('');
  const [previewLoading, setPreviewLoading] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [previewSignature, setPreviewSignature] = useState('');
  const [excludedPreviewEmails, setExcludedPreviewEmails] = useState([]);
  const [sending, setSending] = useState(false);
  const [broadcastLists, setBroadcastLists] = useState([]);
  const [broadcastListDrafts, setBroadcastListDrafts] = useState({});
  const [broadcastListMessage, setBroadcastListMessage] = useState(null);
  const [newBroadcastListName, setNewBroadcastListName] = useState('');
  const [messageTemplates, setMessageTemplates] = useState([]);
  const [newMessageTemplateName, setNewMessageTemplateName] = useState('');
  const [imageBlock, setImageBlock] = useState({ url: '', width: '100', align: 'center' });
  const [buttonBlock, setButtonBlock] = useState({ text: '', url: '', background: '#111827', color: '#ffffff' });
  const [highlightBlock, setHighlightBlock] = useState({ background: '#f8fafc', border: '#d1d5db', padding: '20' });
  const [separatorBlock, setSeparatorBlock] = useState({ color: '#d1d5db', height: '1', margin: '24' });
  const [activeQuickBlock, setActiveQuickBlock] = useState('');

  const showToast = useCallback((text, type = 'success') => {
    const id = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
    const nextToast = { id, text, type, hiding: false };

    setToasts((current) => [...current, nextToast]);
    setTimeout(() => {
      setToasts((current) => current.map((toast) => (toast.id === id ? { ...toast, hiding: true } : toast)));
    }, 2800);
    setTimeout(() => {
      setToasts((current) => current.filter((toast) => toast.id !== id));
    }, 3200);
  }, []);

  const [searchTerm, setSearchTerm] = useState('');
  const [topItemsBySource, setTopItemsBySource] = useState({ product: [], role: [], mailmint: [], broadcast_list: [] });
  const [searchResults, setSearchResults] = useState([]);
  const [selectedBySource, setSelectedBySource] = useState({ product: [], role: [], mailmint: [], broadcast_list: [] });
  const [labelsBySource, setLabelsBySource] = useState({ product: {}, role: {}, mailmint: {}, broadcast_list: {} });

  const rawSourceOptions = useMemo(() => mapOptionsFromSelect('pbm_recipient_source'), []);
  const sourceOptions = useMemo(() => rawSourceOptions.filter((item) => {
    if (!item.disabled) {
      return true;
    }

    return item.value === 'broadcast_list' && broadcastLists.length > 0;
  }), [rawSourceOptions, broadcastLists]);

  useEffect(() => {
    if (sourceOptions.length > 0 && !sourceOptions.some((item) => item.value === source)) {
      setSource(sourceOptions[0].value);
    }
  }, [source, sourceOptions]);

  useEffect(() => {
    syncSelectValue('pbm_recipient_source', source);
    setSearchTerm('');
    setSearchResults([]);
  }, [source]);

  const loadBroadcastLists = async () => {
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_list_broadcast_lists');
    params.append('nonce', nonce);
    const data = await postAjax(params);
    const items = data?.success && Array.isArray(data.data?.items) ? data.data.items : [];
    setBroadcastLists(items);
    setLabelsBySource((prev) => ({
      ...prev,
      broadcast_list: items.reduce((acc, item) => ({ ...acc, [item.id]: `${item.name} (${(item.emails || []).length})` }), {}),
    }));
    setTopItemsBySource((prev) => ({
      ...prev,
      broadcast_list: items.map((item) => ({ value: item.id, label: `${item.name} (${(item.emails || []).length})` })),
    }));
  };

  useEffect(() => {
    loadBroadcastLists();
  }, []);

  const loadMessageTemplates = async () => {
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_list_message_templates');
    params.append('nonce', nonce);
    const data = await postAjax(params);
    const items = data?.success && Array.isArray(data.data?.items) ? data.data.items : [];
    setMessageTemplates(items);
  };

  useEffect(() => {
    loadMessageTemplates();
  }, []);

  useEffect(() => {
    setBroadcastListDrafts((current) => {
      const next = { ...current };
      broadcastLists.forEach((list) => {
        if (!next[list.id]) {
          next[list.id] = {
            name: list.name || '',
            emailsText: (list.emails || []).join('\n'),
          };
        }
      });
      return next;
    });
  }, [broadcastLists]);

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
      showToast(__('Debes seleccionar al menos un elemento.', 'wc-pbm'), 'warning');
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

      let label = labelsBySource[source]?.[selectorValue] || '';
      if (!label && source === 'broadcast_list') {
        const list = broadcastLists.find((item) => item.id === selectorValue);
        if (list) {
          label = `${list.name} (${(list.emails || []).length})`;
        }
      }
      label = label || selectorValue;
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

    showToast(__('Elementos añadidos a la lista global.', 'wc-pbm'), 'success');
  };

  const addManualEmails = (rawInput) => {
    const parsed = parseEmails(rawInput);
    if (parsed.length === 0) {
      showToast(__('No hay emails para añadir.', 'wc-pbm'), 'warning');
      return;
    }

    const validEmails = parsed.filter(isValidEmail);
    const invalidCount = parsed.length - validEmails.length;

    if (validEmails.length === 0) {
      showToast(__('Todos los emails son inválidos.', 'wc-pbm'), 'warning');
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
      showToast(__('Los emails manuales ya estaban añadidos.', 'wc-pbm'), 'warning');
      return;
    }

    setManualEmails((prev) => [...prev, ...uniqueToAdd]);

    if (invalidCount > 0) {
      showToast(__('Algunos emails eran inválidos y no se añadieron.', 'wc-pbm'), 'warning');
      return;
    }

    showToast(__('Emails manuales añadidos.', 'wc-pbm'), 'success');
  };

  const removeFromGlobalAudience = (key) => {
    if ('manual:group' === key) {
      setManualEmails([]);
      return;
    }

    setGlobalAudience((prev) => prev.filter((item) => item.key !== key));
    setItemDetails((prev) => {
      const next = { ...prev };
      delete next[key];
      return next;
    });
  };

  const clearGlobalAudience = () => {
    setGlobalAudience([]);
    setManualEmails([]);
    setItemDetails({});
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

  const currentPreviewSignature = useMemo(() => buildPreviewSignature({
    globalAudience,
    manualEmails,
    batchSize,
    emailsPerHour,
  }), [globalAudience, manualEmails, batchSize, emailsPerHour]);

  const isPreviewStale = Boolean(previewData && previewSignature !== currentPreviewSignature);

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
    setExcludedPreviewEmails([]);
    setPreviewData(null);
    setPreviewSignature('');
  }, [currentPreviewSignature]);

  useEffect(() => {
    if (!scheduleEnabled) {
      setAudienceMode('fixed');
    }
  }, [scheduleEnabled]);

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
      showToast(__('Añade destinatarios antes de previsualizar.', 'wc-pbm'), 'warning');
      return;
    }

    const signature = currentPreviewSignature;
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
    params.append('excluded_emails', JSON.stringify(excludedPreviewEmails));
    params.append('nonce', nonce);

    const data = await postAjax(params);
    setPreviewLoading(false);

    if (!data || !data.success || !data.data) {
      showToast(data?.data?.message || __('Error al obtener destinatarios', 'wc-pbm'), 'error');
      return;
    }

    setPreviewData(data.data);
    setPreviewSignature(signature);
  };

  const removePreviewEmail = (email) => {
    const normalized = String(email || '').toLowerCase();
    setExcludedPreviewEmails((prev) => uniqueEmails([...prev, normalized]));
    setPreviewData((prev) => {
      if (!prev || !Array.isArray(prev.emails)) {
        return prev;
      }
      const emails = prev.emails.filter((item) => String(item).toLowerCase() !== normalized);
      return { ...prev, emails, total: emails.length };
    });
  };

  const saveCurrentPreviewAsBroadcastList = async () => {
    const emails = uniqueEmails(previewData?.emails || []);
    if (emails.length === 0) {
      showToast(__('No hay emails en la vista previa para guardar.', 'wc-pbm'), 'warning');
      return;
    }
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_save_broadcast_list');
    params.append('name', newBroadcastListName);
    params.append('emails', JSON.stringify(emails));
    params.append('nonce', nonce);
    const data = await postAjax(params);
    if (!data?.success) {
      showToast(data?.data?.message || __('No se pudo guardar la lista.', 'wc-pbm'), 'error');
      return;
    }
    setNewBroadcastListName('');
    await loadBroadcastLists();
    showToast(data.data?.message || __('Broadcast List guardada.', 'wc-pbm'), 'success');
  };

  const updateBroadcastList = async (list, nextEmails = null, nextName = null) => {
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_update_broadcast_list');
    params.append('id', list.id);
    params.append('name', nextName ?? list.name);
    params.append('emails', JSON.stringify(nextEmails ?? list.emails ?? []));
    params.append('nonce', nonce);
    const data = await postAjax(params);
    if (!data?.success) {
      showToast(data?.data?.message || __('No se pudo actualizar la lista.', 'wc-pbm'), 'error');
      return;
    }
    await loadBroadcastLists();
    setBroadcastListMessage({ type: 'success', text: data.data?.message || __('Broadcast List actualizada.', 'wc-pbm') });
  };

  const deleteBroadcastList = async (list) => {
    if (!window.confirm(__('¿Borrar esta Broadcast List?', 'wc-pbm'))) {
      return;
    }
    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_delete_broadcast_list');
    params.append('id', list.id);
    params.append('nonce', nonce);
    const data = await postAjax(params);
    if (!data?.success) {
      showToast(data?.data?.message || __('No se pudo borrar la lista.', 'wc-pbm'), 'error');
      return;
    }
    await loadBroadcastLists();
    setBroadcastListMessage({ type: 'success', text: data.data?.message || __('Broadcast List eliminada.', 'wc-pbm') });
  };

  const updateBroadcastListDraft = (id, field, value) => {
    setBroadcastListDrafts((current) => ({
      ...current,
      [id]: {
        ...(current[id] || {}),
        [field]: value,
      },
    }));
  };

  const removeEmailFromBroadcastListDraft = (list, email) => {
    const draft = broadcastListDrafts[list.id] || { emailsText: (list.emails || []).join('\n') };
    const emails = parseEmails(draft.emailsText).filter((item) => item !== email);
    updateBroadcastListDraft(list.id, 'emailsText', emails.join('\n'));
  };

  const renderBroadcastListSettings = () => {
    if (broadcastLists.length === 0) {
      return null;
    }

    return (
      <div className="pbm-broadcast-list-manager">
        <h3>{__('Broadcast Lists guardadas', 'wc-pbm')}</h3>
        <p className="description">{__('Gestiona listas guardadas: cambia nombre, borra emails y pulsa Actualizar lista para guardar.', 'wc-pbm')}</p>
        {broadcastListMessage && (
          <div className={`pbm-react-notice pbm-react-notice-${broadcastListMessage.type || 'success'}`}>{broadcastListMessage.text}</div>
        )}
        {broadcastLists.map((list) => {
          const draft = broadcastListDrafts[list.id] || {
            name: list.name || '',
            emailsText: (list.emails || []).join('\n'),
          };
          const draftEmails = parseEmails(draft.emailsText);
          return (
            <div className="pbm-broadcast-list-card" key={list.id}>
              <TextControl label={__('Nombre', 'wc-pbm')} value={draft.name || ''} onChange={(value) => updateBroadcastListDraft(list.id, 'name', value)} />
              <p>{draftEmails.length} {__('emails', 'wc-pbm')}</p>
              <div className="pbm-broadcast-list-emails">
                {draftEmails.map((email) => (
                  <span className="pbm-preview-email-chip" key={email}>
                    {email}
                    <button type="button" onClick={() => removeEmailFromBroadcastListDraft(list, email)}>×</button>
                  </span>
                ))}
              </div>
              <div className="pbm-broadcast-list-actions">
                <Button variant="secondary" onClick={() => updateBroadcastList(list, draftEmails, draft.name || list.name)}>{__('Actualizar lista', 'wc-pbm')}</Button>
                <Button variant="link" isDestructive onClick={() => deleteBroadcastList(list)}>{__('Borrar lista', 'wc-pbm')}</Button>
              </div>
            </div>
          );
        })}
      </div>
    );
  };

  const saveCurrentMessageTemplate = async () => {
    const content = getClassicEditorMessage();
    if (!content.trim()) {
      showToast(__('No hay contenido de mensaje para guardar.', 'wc-pbm'), 'warning');
      return;
    }

    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_save_message_template');
    params.append('name', newMessageTemplateName);
    params.append('subject', subject);
    params.append('content', content);
    params.append('nonce', nonce);
    const data = await postAjax(params);

    if (!data?.success) {
      showToast(data?.data?.message || __('No se pudo guardar la plantilla.', 'wc-pbm'), 'error');
      return;
    }

    setNewMessageTemplateName('');
    await loadMessageTemplates();
    showToast(data.data?.message || __('Plantilla guardada.', 'wc-pbm'), 'success');
  };

  const loadMessageTemplate = (template) => {
    setSubject(template.subject || '');
    setClassicEditorMessage(template.content || '');
    showToast(__('Plantilla cargada en el asunto y mensaje.', 'wc-pbm'), 'success');
  };

  const deleteMessageTemplate = async (template) => {
    if (!window.confirm(__('¿Borrar esta plantilla?', 'wc-pbm'))) {
      return;
    }

    const nonceField = document.getElementById('pbm_nonce');
    const nonce = nonceField ? nonceField.value : '';
    const params = new URLSearchParams();
    params.append('action', 'pbm_delete_message_template');
    params.append('id', template.id);
    params.append('nonce', nonce);
    const data = await postAjax(params);

    if (!data?.success) {
      showToast(data?.data?.message || __('No se pudo borrar la plantilla.', 'wc-pbm'), 'error');
      return;
    }

    await loadMessageTemplates();
    showToast(data.data?.message || __('Plantilla eliminada.', 'wc-pbm'), 'success');
  };

  const insertImageBlock = () => {
    if (!imageBlock.url.trim()) {
      return;
    }
    insertIntoClassicEditor(`<p style="text-align:${imageBlock.align};"><img src="${imageBlock.url.trim()}" alt="" style="max-width:100%;width:${parseInt(imageBlock.width || '100', 10) || 100}%;height:auto;" /></p>`);
    setActiveQuickBlock('');
  };

  const chooseImageFromMediaLibrary = () => {
    if (!window.wp || !window.wp.media) {
      return;
    }

    const frame = window.wp.media({
      title: __('Seleccionar imagen', 'wc-pbm'),
      button: { text: __('Usar imagen', 'wc-pbm') },
      multiple: false,
    });

    frame.on('select', () => {
      const attachment = frame.state().get('selection').first().toJSON();
      setImageBlock((prev) => ({ ...prev, url: attachment.url || '' }));
    });

    frame.open();
  };

  const insertButtonBlock = () => {
    if (!buttonBlock.text.trim() || !buttonBlock.url.trim()) {
      return;
    }
    insertIntoClassicEditor(`<p style="text-align:center;"><a href="${buttonBlock.url.trim()}" style="display:inline-block;background:${buttonBlock.background};color:${buttonBlock.color};padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:600;">${buttonBlock.text.trim()}</a></p>`);
    setActiveQuickBlock('');
  };

  const insertHighlightBlock = () => {
    insertIntoClassicEditor(`<div style="background:${highlightBlock.background};border:1px solid ${highlightBlock.border};padding:${parseInt(highlightBlock.padding || '20', 10) || 20}px;border-radius:8px;"><p>${__('Escribe aquí el contenido destacado.', 'wc-pbm')}</p></div>`);
    setActiveQuickBlock('');
  };

  const insertSeparatorBlock = () => {
    insertIntoClassicEditor(`<hr style="border:0;background:${separatorBlock.color};height:${parseInt(separatorBlock.height || '1', 10) || 1}px;margin:${parseInt(separatorBlock.margin || '24', 10) || 24}px 0;" />`);
    setActiveQuickBlock('');
  };

  const broadcastListSettingsNode = document.getElementById('pbm-broadcast-list-settings');

  const sendBroadcast = async () => {
    if (listItems.length === 0) {
      showToast(__('Añade destinatarios antes de enviar.', 'wc-pbm'), 'warning');
      return;
    }
    if (!previewData || Number(previewData.total || 0) < 1) {
      showToast(__('Primero debes hacer una vista previa.', 'wc-pbm'), 'warning');
      return;
    }
    if (isPreviewStale) {
      showToast(__('La audiencia o configuración cambió. Actualiza la vista previa antes de enviar.', 'wc-pbm'), 'warning');
      return;
    }

    const messageContent = getClassicEditorMessage();
    if (!subject.trim() || !messageContent.trim()) {
      showToast(__('Asunto y mensaje son obligatorios.', 'wc-pbm'), 'warning');
      return;
    }

    if (scheduleEnabled && !scheduledDatetime) {
      showToast(__('Debes indicar fecha y hora para programar.', 'wc-pbm'), 'warning');
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
    params.append('excluded_emails', JSON.stringify(excludedPreviewEmails));
    params.append('subject', subject);
    params.append('message', messageContent);
    params.append('batch_size', String(parseInt(batchSize || '30', 10) || 30));
    params.append('emails_per_hour', String(parseInt(emailsPerHour || '200', 10) || 200));
    params.append('schedule_enabled', scheduleEnabled ? '1' : '0');
    params.append('audience_mode', scheduleEnabled ? audienceMode : 'fixed');
    params.append('plain_body', plainBody ? '1' : '0');
    params.append('scheduled_datetime', scheduledDatetime);
    params.append('nonce', nonce);

    const data = await postAjax(params);
    setSending(false);

    if (!data || !data.success) {
      showToast(data?.data?.message || __('Error al programar el envío', 'wc-pbm'), 'error');
      return;
    }

    showToast(data.data.message || __('Envío creado correctamente', 'wc-pbm'), 'success');
    setTimeout(() => window.location.reload(), 900);
  };

  return (
    <Card className="pbm-react-shell">
      <CardBody>
        <div className="pbm-react-audience-grid">
          <div className="pbm-react-audience-sources">
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
            />
          </div>
          <div className="pbm-react-audience-manual">
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
              {isPreviewStale && (
                <div className="pbm-react-notice pbm-react-notice-warning">
                  {__('La audiencia o configuración cambió. Actualiza la vista previa antes de enviar.', 'wc-pbm')}
                </div>
              )}
            </div>
          </div>
        </div>
        <div className="pbm-react-send-config">
          {previewData && (
            <div className="pbm-react-preview-box">
              <p><strong>{__('Total de destinatarios únicos:', 'wc-pbm')}</strong> {previewData.total || 0}</p>
              <p><strong>{__('Fuente:', 'wc-pbm')}</strong> {__('Lista global combinada', 'wc-pbm')}</p>
              <div className="pbm-react-preview-emails">
                {(previewData.emails || []).map((email) => (
                  <span className="pbm-preview-email-chip" key={email}>
                    {email}
                    <button type="button" onClick={() => removePreviewEmail(email)} aria-label={__('Quitar email de este envío', 'wc-pbm')}>×</button>
                  </span>
                ))}
              </div>
              <div className="pbm-broadcast-list-save">
                <TextControl label={__('Nombre para Broadcast List', 'wc-pbm')} value={newBroadcastListName} onChange={setNewBroadcastListName} placeholder={__('Opcional: si queda vacío se usa fecha/hora', 'wc-pbm')} />
                <Button variant="secondary" onClick={saveCurrentPreviewAsBroadcastList}>{__('Guardar como Broadcast List', 'wc-pbm')}</Button>
              </div>
            </div>
          )}
          <TextControl label={__('Asunto', 'wc-pbm')} value={subject} onChange={setSubject} />
          <div className="pbm-react-classic-editor">
            <label htmlFor="pbm_message">{__('Mensaje', 'wc-pbm')}</label>
            <div ref={messageHostRef} />
          </div>
          <div className="pbm-message-template-box">
            <h3>{__('Añadir bloques', 'wc-pbm')}</h3>
            <div className="pbm-broadcast-list-actions">
              <Button variant={activeQuickBlock === 'image' ? 'primary' : 'secondary'} onClick={() => setActiveQuickBlock(activeQuickBlock === 'image' ? '' : 'image')}>{__('Imagen', 'wc-pbm')}</Button>
              <Button variant={activeQuickBlock === 'button' ? 'primary' : 'secondary'} onClick={() => setActiveQuickBlock(activeQuickBlock === 'button' ? '' : 'button')}>{__('Botón', 'wc-pbm')}</Button>
              <Button variant={activeQuickBlock === 'highlight' ? 'primary' : 'secondary'} onClick={() => setActiveQuickBlock(activeQuickBlock === 'highlight' ? '' : 'highlight')}>{__('Bloque', 'wc-pbm')}</Button>
              <Button variant={activeQuickBlock === 'separator' ? 'primary' : 'secondary'} onClick={() => setActiveQuickBlock(activeQuickBlock === 'separator' ? '' : 'separator')}>{__('Separador', 'wc-pbm')}</Button>
            </div>
            {activeQuickBlock === 'image' && (
              <div className="pbm-react-send-grid">
                <TextControl label={__('URL imagen', 'wc-pbm')} value={imageBlock.url} onChange={(url) => setImageBlock((prev) => ({ ...prev, url }))} />
                <TextControl label={__('Ancho imagen %', 'wc-pbm')} type="number" min="10" max="100" value={imageBlock.width} onChange={(width) => setImageBlock((prev) => ({ ...prev, width }))} />
                <TextControl label={__('Alineación', 'wc-pbm')} value={imageBlock.align} onChange={(align) => setImageBlock((prev) => ({ ...prev, align }))} />
                <Button variant="secondary" onClick={chooseImageFromMediaLibrary}>{__('Elegir imagen', 'wc-pbm')}</Button>
                <Button variant="primary" onClick={insertImageBlock}>{__('Insertar', 'wc-pbm')}</Button>
              </div>
            )}
            {activeQuickBlock === 'button' && (
              <div className="pbm-react-send-grid">
                <TextControl label={__('Texto botón', 'wc-pbm')} value={buttonBlock.text} onChange={(text) => setButtonBlock((prev) => ({ ...prev, text }))} />
                <TextControl label={__('URL botón', 'wc-pbm')} value={buttonBlock.url} onChange={(url) => setButtonBlock((prev) => ({ ...prev, url }))} />
                <TextControl label={__('Color fondo', 'wc-pbm')} type="color" value={buttonBlock.background} onChange={(background) => setButtonBlock((prev) => ({ ...prev, background }))} />
                <TextControl label={__('Color texto', 'wc-pbm')} type="color" value={buttonBlock.color} onChange={(color) => setButtonBlock((prev) => ({ ...prev, color }))} />
                <Button variant="primary" onClick={insertButtonBlock}>{__('Insertar', 'wc-pbm')}</Button>
              </div>
            )}
            {activeQuickBlock === 'highlight' && (
              <div className="pbm-react-send-grid">
                <TextControl label={__('Fondo', 'wc-pbm')} type="color" value={highlightBlock.background} onChange={(background) => setHighlightBlock((prev) => ({ ...prev, background }))} />
                <TextControl label={__('Borde', 'wc-pbm')} type="color" value={highlightBlock.border} onChange={(border) => setHighlightBlock((prev) => ({ ...prev, border }))} />
                <TextControl label={__('Padding', 'wc-pbm')} type="number" min="0" max="80" value={highlightBlock.padding} onChange={(padding) => setHighlightBlock((prev) => ({ ...prev, padding }))} />
                <Button variant="primary" onClick={insertHighlightBlock}>{__('Insertar', 'wc-pbm')}</Button>
              </div>
            )}
            {activeQuickBlock === 'separator' && (
              <div className="pbm-react-send-grid">
                <TextControl label={__('Color', 'wc-pbm')} type="color" value={separatorBlock.color} onChange={(color) => setSeparatorBlock((prev) => ({ ...prev, color }))} />
                <TextControl label={__('Altura', 'wc-pbm')} type="number" min="1" max="20" value={separatorBlock.height} onChange={(height) => setSeparatorBlock((prev) => ({ ...prev, height }))} />
                <TextControl label={__('Margen', 'wc-pbm')} type="number" min="0" max="80" value={separatorBlock.margin} onChange={(margin) => setSeparatorBlock((prev) => ({ ...prev, margin }))} />
                <Button variant="primary" onClick={insertSeparatorBlock}>{__('Insertar', 'wc-pbm')}</Button>
              </div>
            )}
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
          {scheduleEnabled && previewData && !isPreviewStale && (
            <div className="pbm-react-audience-mode">
              <RadioControl
                label={__('Audiencia programada', 'wc-pbm')}
                selected={audienceMode}
                options={[
                  { label: __('Fija: usar los destinatarios exactos de esta vista previa', 'wc-pbm'), value: 'fixed' },
                  { label: __('Dinámica: recalcular la audiencia al ejecutar', 'wc-pbm'), value: 'dynamic' },
                ]}
                onChange={setAudienceMode}
              />
              {audienceMode === 'dynamic' && (
                <p className="description">
                  {__('Las fuentes vivas se recalcularán en la fecha programada. Los emails quitados de la vista previa seguirán excluidos.', 'wc-pbm')}
                </p>
              )}
            </div>
          )}
          <div className="pbm-react-estimate">
            <strong>{__('Resumen estimado:', 'wc-pbm')}</strong>{' '}
            {`${deliveryEstimate.unique} ${__('únicos', 'wc-pbm')} · ${deliveryEstimate.batches} ${__('lotes', 'wc-pbm')} · ${deliveryEstimate.intervalMinutes} ${__('min entre lotes', 'wc-pbm')} · ${formatEstimatedDuration(deliveryEstimate.totalWindowMinutes)}`}
          </div>
          <div className="pbm-react-send-actions">
            <CheckboxControl
              label={__('Enviar HTML sin plantilla global', 'wc-pbm')}
              checked={plainBody}
              onChange={setPlainBody}
            />
            <Button variant="primary" onClick={sendBroadcast} disabled={sending || listItems.length === 0 || isPreviewStale}>
              {sending ? __('Programando envíos...', 'wc-pbm') : __('Enviar Emails', 'wc-pbm')}
            </Button>
          </div>
          <div className="pbm-message-template-box">
            <h3>{__('Plantillas', 'wc-pbm')}</h3>
            <div className="pbm-broadcast-list-save">
              <TextControl hideLabelFromVision label={__('Nombre de plantilla', 'wc-pbm')} value={newMessageTemplateName} onChange={setNewMessageTemplateName} placeholder={__('Nombre de plantilla', 'wc-pbm')} />
              <Button variant="secondary" onClick={saveCurrentMessageTemplate}>{__('Guardar asunto y body', 'wc-pbm')}</Button>
            </div>
            {messageTemplates.length > 0 && (
              <div className="pbm-message-template-list">
                {messageTemplates.map((template) => (
                  <div className="pbm-message-template-item" key={template.id}>
                    <strong>{template.name}</strong>
                    {template.subject && <small>{template.subject}</small>}
                    <Button variant="secondary" onClick={() => loadMessageTemplate(template)}>{__('Cargar', 'wc-pbm')}</Button>
                    <Button variant="link" isDestructive onClick={() => deleteMessageTemplate(template)}>{__('Borrar', 'wc-pbm')}</Button>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
        <ScheduledLogsPanel showToast={showToast} />
        <ToastViewport toasts={toasts} />
        {broadcastListSettingsNode ? createPortal(renderBroadcastListSettings(), broadcastListSettingsNode) : null}
      </CardBody>
    </Card>
  );
}
