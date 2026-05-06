import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function GlobalAudienceList({ items, onRemove, onClear, summary }) {
  const [expandedKeys, setExpandedKeys] = useState([]);

  const toggleExpand = (key) => {
    setExpandedKeys((prev) => (prev.includes(key) ? prev.filter((item) => item !== key) : [...prev, key]));
  };

  const isExpanded = (key) => expandedKeys.includes(key);

  return (
    <div className="pbm-react-global-list">
      <div className="pbm-react-global-list-header">
        <h3>{__('Lista global de audiencias', 'wc-pbm')}</h3>
        <Button variant="tertiary" onClick={onClear} disabled={items.length === 0}>
          {__('Limpiar', 'wc-pbm')}
        </Button>
      </div>

      {summary && items.length > 0 && (
        <div className="pbm-react-summary">
          <span><strong>{__('Bruto', 'wc-pbm')}:</strong> {summary.gross}</span>
          <span><strong>{__('Únicos', 'wc-pbm')}:</strong> {summary.unique}</span>
          <span><strong>{__('Duplicados', 'wc-pbm')}:</strong> {summary.duplicates}</span>
        </div>
      )}

      {items.length === 0 && <p className="description">{__('Aún no hay elementos añadidos.', 'wc-pbm')}</p>}

      {items.length > 0 && (
        <ul className="pbm-react-global-list-items">
          {items.map((item) => (
            <li key={item.key}>
              <div className="pbm-list-main">
                <span>
                  <strong>{item.sourceLabel}:</strong> {item.selectorLabel}
                  <em className="pbm-count-pill">{item.count ?? 0}</em>
                </span>
                <span className="pbm-list-actions">
                  {Array.isArray(item.emails) && item.emails.length > 0 && (
                    <Button variant="tertiary" onClick={() => toggleExpand(item.key)}>
                      {isExpanded(item.key) ? __('Ocultar', 'wc-pbm') : __('Ver', 'wc-pbm')}
                    </Button>
                  )}
                  <Button variant="link" isDestructive onClick={() => onRemove(item.key)}>
                    {__('Quitar', 'wc-pbm')}
                  </Button>
                </span>
              </div>
              {Array.isArray(item.emails) && item.emails.length > 0 && isExpanded(item.key) && (
                <div className="pbm-list-detail">
                  {item.emails.join(', ')}
                </div>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
