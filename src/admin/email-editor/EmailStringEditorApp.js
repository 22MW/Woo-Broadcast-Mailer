import { Button, Card, CardBody, CheckboxControl, Notice, SelectControl, Spinner, TextControl, TextareaControl } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { emailEditorRequest } from './api';

const HIDDEN_MARKER = '__pbm_hidden__';

function isHiddenValue(value) {
  return value === HIDDEN_MARKER;
}

function buildTemplateOptions(templates) {
  return [
    { label: __('Todas las plantillas al buscar', 'wc-pbm'), value: '' },
    ...templates.map((template) => ({
      label: `${template.sourceLabel} · ${template.label}`,
      value: template.id,
    })),
  ];
}

function Message({ message, onRemove }) {
  if (!message) {
    return null;
  }

  return (
    <Notice status={message.type || 'info'} isDismissible onRemove={onRemove}>
      {message.text}
    </Notice>
  );
}

function EditorTab({ templates, languages, onMessage }) {
  const [template, setTemplate] = useState('');
  const [search, setSearch] = useState('');
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  const templateOptions = useMemo(() => buildTemplateOptions(templates), [templates]);

  const searchStrings = async () => {
    if (!template && !search.trim()) {
      onMessage({ type: 'warning', text: __('Elige una plantilla o escribe una búsqueda.', 'wc-pbm') });
      return;
    }

    setLoading(true);
    try {
      const data = await emailEditorRequest('pbm_email_editor_search_strings', { template, search });
      setItems(data.items || []);
      onMessage({ type: 'success', text: __('Búsqueda completada.', 'wc-pbm') });
    } catch (error) {
      onMessage({ type: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  };

  const updateItemCustom = (index, language, value) => {
    setItems((current) => current.map((item, itemIndex) => {
      if (itemIndex !== index) {
        return item;
      }

      return {
        ...item,
        custom: {
          ...(item.custom || {}),
          [language]: value,
        },
      };
    }));
  };

  const updateItemHidden = (index, language, hidden) => {
    updateItemCustom(index, language, hidden ? HIDDEN_MARKER : '');
  };

  const saveStrings = async () => {
    setSaving(true);
    try {
      const payload = items.map((item) => ({
        template: item.templateId,
        original: item.original,
        custom: item.custom || {},
      }));
      const data = await emailEditorRequest('pbm_email_editor_save_strings', { items: payload });
      onMessage({ type: 'success', text: data.message || __('Cambios guardados.', 'wc-pbm') });
    } catch (error) {
      onMessage({ type: 'error', text: error.message });
    } finally {
      setSaving(false);
    }
  };

  return (
    <Card className="pbm-react-send-config">
      <CardBody>
        <div className="pbm-email-editor-filters">
          <SelectControl
            label={__('Email / plantilla', 'wc-pbm')}
            value={template}
            options={templateOptions}
            onChange={setTemplate}
          />
          <TextControl
            label={__('Buscar', 'wc-pbm')}
            placeholder={__('Si no eliges plantilla, la búsqueda recorre todas las plantillas permitidas.', 'wc-pbm')}
            value={search}
            onChange={setSearch}
            onKeyDown={(event) => {
              if (event.key === 'Enter' && !loading) {
                event.preventDefault();
                searchStrings();
              }
            }}
          />
          <Button variant="secondary" className="pbm-react-source-btn" onClick={searchStrings} disabled={loading}>
            {loading ? __('Buscando...', 'wc-pbm') : __('Buscar / cargar strings', 'wc-pbm')}
          </Button>
        </div>

        {loading && <Spinner />}

        {items.length > 0 && (
          <>
            <div className="pbm-email-editor-results">
              {items.map((item, index) => (
                <section className="pbm-email-editor-result-card" key={`${item.templateId}-${item.original}`}>
                  <div className="pbm-email-editor-result-meta">
                    <div>
                      <span className="pbm-email-editor-label">Plantilla</span>
                      <strong>{item.templateLabel}</strong>
                      <small>{item.sourceLabel} · {item.relativePath}</small>
                      {'dynamic' === item.relativePath ? (
                        <span className="pbm-email-editor-dynamic-label">Dinámico</span>
                      ) : null}
                    </div>
                    <div>
                      <span className="pbm-email-editor-label">Original</span>
                      <code>{item.original}</code>
                    </div>
                    <div>
                      <span className="pbm-email-editor-label">Función</span>
                      <code>{item.function}</code>
                    </div>
                  </div>
                  <div className="pbm-email-editor-language-list">
                    <h4>{__('Personalización por idioma', 'wc-pbm')}</h4>
                    {languages.map((language) => (
                      <div key={language.code} className="pbm-email-editor-language-control">
                        <TextareaControl
                          label={`${language.label} (${language.code})`}
                          value={isHiddenValue(item.custom?.[language.code]) ? '' : item.custom?.[language.code] || ''}
                          placeholder={item.translations?.[language.code] || item.original}
                          help={`${__('Traducción actual:', 'wc-pbm')} ${item.translations?.[language.code] || item.original}`}
                          disabled={isHiddenValue(item.custom?.[language.code])}
                          onChange={(value) => updateItemCustom(index, language.code, value)}
                        />
                        <CheckboxControl
                          label={__('Ocultar este texto', 'wc-pbm')}
                          checked={isHiddenValue(item.custom?.[language.code])}
                          onChange={(checked) => updateItemHidden(index, language.code, checked)}
                        />
                      </div>
                    ))}
                  </div>
                </section>
              ))}
            </div>
            <p>
              <Button variant="primary" onClick={saveStrings} disabled={saving}>
                {saving ? __('Guardando...', 'wc-pbm') : __('Guardar personalizaciones', 'wc-pbm')}
              </Button>
            </p>
          </>
        )}

        {!loading && items.length === 0 && (
          <p>{__('Selecciona una plantilla o busca una palabra para ver strings editables.', 'wc-pbm')}</p>
        )}
      </CardBody>
    </Card>
  );
}

function ChangesTab({ refreshKey, onMessage }) {
  const [changes, setChanges] = useState([]);
  const [loading, setLoading] = useState(false);
  const [savingKey, setSavingKey] = useState('');
  const [drafts, setDrafts] = useState({});

  const loadChanges = async () => {
    setLoading(true);
    try {
      const data = await emailEditorRequest('pbm_email_editor_list_changes');
      setChanges(data.changes || []);
      const nextDrafts = {};
      (data.changes || []).forEach((change) => {
        nextDrafts[`${change.language}:${change.original}`] = change.custom || '';
      });
      setDrafts(nextDrafts);
    } catch (error) {
      onMessage({ type: 'error', text: error.message });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadChanges();
  }, [refreshKey]);

  const updateChange = async (change) => {
    const key = `${change.language}:${change.original}`;
    setSavingKey(key);
    try {
      const data = await emailEditorRequest('pbm_email_editor_update_string', {
        language: change.language,
        template: change.template || '',
        original: change.original,
        custom: drafts[key] || '',
      });
      onMessage({ type: 'success', text: data.message || __('Cambio guardado.', 'wc-pbm') });
      await loadChanges();
    } catch (error) {
      onMessage({ type: 'error', text: error.message });
    } finally {
      setSavingKey('');
    }
  };

  const updateDraftHidden = (key, hidden) => {
    setDrafts((current) => ({
      ...current,
      [key]: hidden ? HIDDEN_MARKER : '',
    }));
  };

  const deleteChange = async (change) => {
    const key = `${change.language}:${change.original}`;
    setSavingKey(key);
    try {
      const data = await emailEditorRequest('pbm_email_editor_delete_string', {
        language: change.language,
        original: change.original,
      });
      onMessage({ type: 'success', text: data.message || __('Personalización eliminada.', 'wc-pbm') });
      await loadChanges();
    } catch (error) {
      onMessage({ type: 'error', text: error.message });
    } finally {
      setSavingKey('');
    }
  };

  if (loading) {
    return <Spinner />;
  }

  if (changes.length === 0) {
    return <p>{__('No hay cambios guardados todavía.', 'wc-pbm')}</p>;
  }

  return (
    <table className="widefat striped wp-list-table pbm-email-editor-table">
      <thead>
        <tr>
          <th>{__('Idioma', 'wc-pbm')}</th>
          <th>{__('Original', 'wc-pbm')}</th>
          <th>{__('Personalizado', 'wc-pbm')}</th>
          <th>{__('Plantilla', 'wc-pbm')}</th>
          <th>{__('Origen', 'wc-pbm')}</th>
          <th>{__('Acciones', 'wc-pbm')}</th>
        </tr>
      </thead>
      <tbody>
        {changes.map((change) => {
          const key = `${change.language}:${change.original}`;
          const isLegacy = change.source === 'legacy';
          return (
            <tr key={key}>
              <td>{change.languageLabel} ({change.language})</td>
              <td><code>{change.original}</code></td>
              <td>
                <TextareaControl
                  value={isHiddenValue(drafts[key]) ? '' : drafts[key] || ''}
                  disabled={isHiddenValue(drafts[key])}
                  onChange={(value) => setDrafts((current) => ({ ...current, [key]: value }))}
                />
                <CheckboxControl
                  label={__('Oculto', 'wc-pbm')}
                  checked={isHiddenValue(drafts[key])}
                  onChange={(checked) => updateDraftHidden(key, checked)}
                />
              </td>
              <td>{change.template || '-'}</td>
              <td>{isLegacy ? __('Legacy', 'wc-pbm') : __('Propio', 'wc-pbm')}</td>
              <td>
                <Button variant="secondary" onClick={() => updateChange(change)} disabled={savingKey === key}>
                  {__('Guardar', 'wc-pbm')}
                </Button>{' '}
                <Button variant="link" isDestructive onClick={() => deleteChange(change)} disabled={savingKey === key || isLegacy}>
                  {__('Borrar', 'wc-pbm')}
                </Button>
              </td>
            </tr>
          );
        })}
      </tbody>
    </table>
  );
}

export default function EmailStringEditorApp() {
  const [templates, setTemplates] = useState([]);
  const [languages, setLanguages] = useState([]);
  const [hasLegacyData, setHasLegacyData] = useState(false);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [activeTab, setActiveTab] = useState('editor');

  useEffect(() => {
    const bootstrap = async () => {
      try {
        const data = await emailEditorRequest('pbm_email_editor_bootstrap');
        setTemplates(data.templates || []);
        setLanguages(data.languages || []);
        setHasLegacyData(Boolean(data.hasLegacyData));
      } catch (error) {
        setMessage({ type: 'error', text: error.message });
      } finally {
        setLoading(false);
      }
    };

    bootstrap();
  }, []);

  const handleMessage = (nextMessage) => {
    setMessage(nextMessage);
    if (nextMessage?.type === 'success') {
      setRefreshKey((current) => current + 1);
    }
  };

  if (loading) {
    return <Spinner />;
  }

  return (
    <div className="pbm-email-editor-react pbm-react-shell">
      <Message message={message} onRemove={() => setMessage(null)} />
      {hasLegacyData && (
        <Notice status="info" isDismissible={false}>
          {__('Se han detectado datos antiguos en wc_custom_email_strings. Se muestran como compatibilidad, sin migración automática.', 'wc-pbm')}
        </Notice>
      )}
      <div className="pbm-react-global-list pbm-email-editor-tabs">
        <p className="pbm-email-editor-intro">
          {__('Editor de emails WooCommerce: busca strings en plantillas de emails y ajusta sus textos por idioma.', 'wc-pbm')}
        </p>
        <div className="pbm-react-source-buttons pbm-email-editor-tab-buttons">
          <Button
            variant={activeTab === 'editor' ? 'primary' : 'secondary'}
            className="pbm-react-source-btn"
            onClick={() => setActiveTab('editor')}
          >
            {__('Editor', 'wc-pbm')}
          </Button>
          <Button
            variant={activeTab === 'changes' ? 'primary' : 'secondary'}
            className="pbm-react-source-btn"
            onClick={() => setActiveTab('changes')}
          >
            {__('Cambios guardados', 'wc-pbm')}
          </Button>
        </div>
        {activeTab === 'changes'
          ? <ChangesTab refreshKey={refreshKey} onMessage={handleMessage} />
          : <EditorTab templates={templates} languages={languages} onMessage={handleMessage} />}
      </div>
    </div>
  );
}
