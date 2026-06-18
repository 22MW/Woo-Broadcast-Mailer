import { Button, Card, CardBody, Notice, SelectControl, Spinner, TabPanel, TextControl, TextareaControl } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { emailEditorRequest } from './api';

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
    <Card>
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
            value={search}
            onChange={setSearch}
            help={__('Si no eliges plantilla, la búsqueda recorre todas las plantillas permitidas.', 'wc-pbm')}
          />
          <Button variant="secondary" onClick={searchStrings} disabled={loading}>
            {loading ? __('Buscando...', 'wc-pbm') : __('Buscar / cargar strings', 'wc-pbm')}
          </Button>
        </div>

        {loading && <Spinner />}

        {items.length > 0 && (
          <>
            <table className="widefat striped pbm-email-editor-table">
              <thead>
                <tr>
                  <th>{__('Plantilla', 'wc-pbm')}</th>
                  <th>{__('Original', 'wc-pbm')}</th>
                  <th>{__('Función', 'wc-pbm')}</th>
                  <th>{__('Personalización por idioma', 'wc-pbm')}</th>
                </tr>
              </thead>
              <tbody>
                {items.map((item, index) => (
                  <tr key={`${item.templateId}-${item.original}`}>
                    <td>
                      <strong>{item.templateLabel}</strong><br />
                      <span>{item.sourceLabel} · {item.relativePath}</span>
                    </td>
                    <td><code>{item.original}</code></td>
                    <td><code>{item.function}</code></td>
                    <td>
                      {languages.map((language) => (
                        <TextareaControl
                          key={language.code}
                          label={`${language.label} (${language.code})`}
                          value={item.custom?.[language.code] || ''}
                          placeholder={item.translations?.[language.code] || item.original}
                          help={`${__('Traducción actual:', 'wc-pbm')} ${item.translations?.[language.code] || item.original}`}
                          onChange={(value) => updateItemCustom(index, language.code, value)}
                        />
                      ))}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
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
    <table className="widefat striped pbm-email-editor-table">
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
                  value={drafts[key] || ''}
                  onChange={(value) => setDrafts((current) => ({ ...current, [key]: value }))}
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
    <div className="pbm-email-editor-react">
      <Message message={message} onRemove={() => setMessage(null)} />
      {hasLegacyData && (
        <Notice status="info" isDismissible={false}>
          {__('Se han detectado datos antiguos en wc_custom_email_strings. Se muestran como compatibilidad, sin migración automática.', 'wc-pbm')}
        </Notice>
      )}
      <TabPanel
        className="pbm-email-editor-tabs"
        tabs={[
          { name: 'editor', title: __('Editor', 'wc-pbm') },
          { name: 'changes', title: __('Cambios guardados', 'wc-pbm') },
        ]}
      >
        {(tab) => (
          tab.name === 'changes'
            ? <ChangesTab refreshKey={refreshKey} onMessage={handleMessage} />
            : <EditorTab templates={templates} languages={languages} onMessage={handleMessage} />
        )}
      </TabPanel>
    </div>
  );
}
