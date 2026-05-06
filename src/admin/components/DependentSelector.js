import { SearchControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SOURCE_CONFIG = {
  product: {
    title: __('Producto Woo', 'wc-pbm'),
    description: __('Selecciona uno o varios productos para la audiencia.', 'wc-pbm'),
  },
  role: {
    title: __('Rol WP', 'wc-pbm'),
    description: __('Selecciona uno o varios roles de WordPress.', 'wc-pbm'),
  },
  mailmint: {
    title: __('Lista Mail Mint', 'wc-pbm'),
    description: __('Selecciona una o varias listas de Mail Mint.', 'wc-pbm'),
  },
};

function renderItemLabel(item, countByKey, source) {
  const key = `${source}:${item.value}`;
  const count = countByKey[key];
  if (typeof count === 'number') {
    return `${item.label} (${count})`;
  }

  return item.label;
}

export default function DependentSelector({
  source,
  topItems,
  searchTerm,
  onSearchTermChange,
  searchResults,
  selectedValues,
  onToggleSelection,
  countByKey,
}) {
  const config = SOURCE_CONFIG[source] || SOURCE_CONFIG.product;

  return (
    <div className="pbm-react-selector">
      <h3>{__('Selector', 'wc-pbm')}</h3>
      <p className="pbm-selector-title">{config.title}</p>

      <div className="pbm-top-items">
        {topItems.map((item) => {
          const isSelected = selectedValues.includes(item.value);
          return (
            <button
              key={item.value}
              type="button"
              className={`pbm-pill ${isSelected ? 'is-selected' : ''}`}
              onClick={() => onToggleSelection(item)}
            >
              {renderItemLabel(item, countByKey, source)}
            </button>
          );
        })}
      </div>

      <SearchControl
        value={searchTerm}
        onChange={onSearchTermChange}
        placeholder={__('Busca otras opciones (3+ letras)…', 'wc-pbm')}
      />

      {searchTerm.length >= 3 && (
        <div className="pbm-search-results">
          {searchResults.map((item) => {
            const isSelected = selectedValues.includes(item.value);
            return (
              <button
                key={item.value}
                type="button"
                className={`pbm-pill ${isSelected ? 'is-selected' : ''}`}
                onClick={() => onToggleSelection(item)}
              >
                {renderItemLabel(item, countByKey, source)}
              </button>
            );
          })}
          {searchResults.length === 0 && <p className="description">{__('Sin resultados', 'wc-pbm')}</p>}
        </div>
      )}

      {selectedValues.length > 0 && (
        <div className="pbm-selected-values">
          {selectedValues.map((value) => (
            <span className="pbm-selected-chip" key={value}>{value}</span>
          ))}
        </div>
      )}

      <p className="description">{config.description}</p>
    </div>
  );
}
