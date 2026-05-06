import { Button } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const SORT_FIELDS = [
  { key: 'scheduled_at', label: __('Fecha', 'wc-pbm') },
  { key: 'status', label: __('Estado', 'wc-pbm') },
  { key: 'subject', label: __('Asunto', 'wc-pbm') },
];

async function postAjax(params) {
  const response = await fetch(window.ajaxurl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: params.toString(),
  });

  return response.json();
}

export default function ScheduledLogsPanel() {
  const [items, setItems] = useState([]);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [sortBy, setSortBy] = useState('scheduled_at');
  const [sortDir, setSortDir] = useState('DESC');
  const [selectedIds, setSelectedIds] = useState([]);
  const [logsHtml, setLogsHtml] = useState('');
  const [showLogs, setShowLogs] = useState(false);

  const scheduledNonce = window.pbmAdminApp?.scheduledNonce || '';

  const loadData = async () => {
    const params = new URLSearchParams();
    params.append('action', 'pbm_list_scheduled_emails');
    params.append('page', String(page));
    params.append('per_page', '12');
    params.append('sort_by', sortBy);
    params.append('sort_dir', sortDir);
    params.append('nonce', scheduledNonce);

    const data = await postAjax(params);
    if (!data || !data.success || !data.data) {
      return;
    }

    setItems(data.data.items || []);
    setTotalPages(Number(data.data.total_pages || 1));
    setSelectedIds([]);
  };

  useEffect(() => {
    loadData();
  }, [page, sortBy, sortDir]);

  const toggleSort = (field) => {
    if (sortBy === field) {
      setSortDir((prev) => (prev === 'ASC' ? 'DESC' : 'ASC'));
      return;
    }
    setSortBy(field);
    setSortDir('DESC');
  };

  const toggleSelect = (id) => {
    setSelectedIds((prev) => (prev.includes(id) ? prev.filter((v) => v !== id) : [...prev, id]));
  };

  const allOnPageSelected = useMemo(() => {
    if (items.length === 0) {
      return false;
    }
    return items.every((item) => selectedIds.includes(item.id));
  }, [items, selectedIds]);

  const toggleSelectAllPage = () => {
    if (allOnPageSelected) {
      setSelectedIds([]);
      return;
    }
    setSelectedIds(items.map((item) => item.id));
  };

  const bulkDeleteSelected = async () => {
    if (selectedIds.length === 0) {
      return;
    }

    if (!window.confirm(__('¿Eliminar seleccionados?', 'wc-pbm'))) {
      return;
    }

    const params = new URLSearchParams();
    params.append('action', 'pbm_bulk_delete_scheduled_ids');
    params.append('nonce', scheduledNonce);
    selectedIds.forEach((id) => params.append('ids[]', String(id)));

    const data = await postAjax(params);
    if (!data || !data.success) {
      window.alert(data?.data?.message || __('Error al borrar', 'wc-pbm'));
      return;
    }

    await loadData();
  };

  const deleteOne = async (id) => {
    if (!window.confirm(__('¿Eliminar este envío y sus logs?', 'wc-pbm'))) {
      return;
    }

    const params = new URLSearchParams();
    params.append('action', 'pbm_delete_scheduled_email');
    params.append('scheduled_id', String(id));
    params.append('nonce', scheduledNonce);

    const data = await postAjax(params);
    if (!data || !data.success) {
      window.alert(data?.data?.message || __('Error al borrar', 'wc-pbm'));
      return;
    }

    await loadData();
  };

  const viewLogs = async (id) => {
    const params = new URLSearchParams();
    params.append('action', 'pbm_get_scheduled_logs');
    params.append('scheduled_id', String(id));
    params.append('nonce', scheduledNonce);

    const data = await postAjax(params);
    if (!data || !data.success) {
      window.alert(data?.data?.message || __('Error al obtener logs', 'wc-pbm'));
      return;
    }

    setLogsHtml(data.data.html || '');
    setShowLogs(true);
  };

  return (
    <div className="pbm-react-scheduled">
      <div className="pbm-react-scheduled-header">
        <h3>{__('Ver envíos programados y logs', 'wc-pbm')}</h3>
        <div className="pbm-react-scheduled-actions">
          <Button variant="secondary" onClick={toggleSelectAllPage}>
            {allOnPageSelected ? __('Deseleccionar todos', 'wc-pbm') : __('Seleccionar todos', 'wc-pbm')}
          </Button>
          <Button variant="secondary" onClick={bulkDeleteSelected} disabled={selectedIds.length === 0}>
            {__('Borrar seleccionados', 'wc-pbm')}
          </Button>
        </div>
      </div>

      <div className="pbm-react-scheduled-sort">
        {SORT_FIELDS.map((field) => (
          <button key={field.key} type="button" className="pbm-sort-btn" onClick={() => toggleSort(field.key)}>
            {field.label} {sortBy === field.key ? (sortDir === 'ASC' ? '▲' : '▼') : ''}
          </button>
        ))}
      </div>

      <div className="pbm-react-scheduled-list">
        {items.map((item) => (
          <div className="pbm-scheduled-card" key={item.id}>
            <div className="pbm-scheduled-card-head">
              <label>
                <input
                  type="checkbox"
                  checked={selectedIds.includes(item.id)}
                  onChange={() => toggleSelect(item.id)}
                />
                <strong>#{item.id}</strong>
              </label>
              <span className={`pbm-status-badge pbm-status-${item.status}`}>{item.status_label}</span>
            </div>
            <div><strong>{__('Tipo', 'wc-pbm')}:</strong> {item.type}</div>
            <div><strong>{__('Fecha', 'wc-pbm')}:</strong> {item.date}</div>
            <div><strong>{__('Audiencia', 'wc-pbm')}:</strong> {item.audience}</div>
            <div><strong>{__('Total mensajes', 'wc-pbm')}:</strong> {item.total_messages ?? 0}</div>
            <div><strong>{__('Asunto', 'wc-pbm')}:</strong> {item.subject}</div>
            <div><strong>{__('Config. Envío', 'wc-pbm')}:</strong> {item.config}</div>
            <div className="pbm-scheduled-card-actions">
              <Button variant="secondary" onClick={() => viewLogs(item.id)}>{__('Ver Logs', 'wc-pbm')}</Button>
              {item.can_delete && (
                <Button variant="secondary" isDestructive onClick={() => deleteOne(item.id)}>
                  {__('Borrar', 'wc-pbm')}
                </Button>
              )}
            </div>
          </div>
        ))}
      </div>

      <div className="pbm-react-pagination">
        <Button variant="secondary" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
          {__('Anterior', 'wc-pbm')}
        </Button>
        <span>{__('Página', 'wc-pbm')} {page} / {totalPages}</span>
        <Button variant="secondary" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>
          {__('Siguiente', 'wc-pbm')}
        </Button>
      </div>

      {showLogs && (
        <div className="pbm-react-logs-modal" onClick={() => setShowLogs(false)}>
          <div className="pbm-react-logs-box" onClick={(e) => e.stopPropagation()}>
            <h4>{__('Logs de envío', 'wc-pbm')}</h4>
            <div dangerouslySetInnerHTML={{ __html: logsHtml }} />
            <p><Button variant="secondary" onClick={() => setShowLogs(false)}>{__('Cerrar', 'wc-pbm')}</Button></p>
          </div>
        </div>
      )}
    </div>
  );
}
