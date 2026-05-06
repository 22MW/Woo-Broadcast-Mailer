import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SOURCE_CONFIG = {
  product: {
    title: __('Producto Woo', 'wc-pbm'),
    description: __('Selecciona el producto. Se incluirán automáticamente variaciones y suscripciones relacionadas.', 'wc-pbm'),
  },
  role: {
    title: __('Rol WP', 'wc-pbm'),
    description: __('Selecciona el rol de WordPress para obtener destinatarios.', 'wc-pbm'),
  },
  mailmint: {
    title: __('Lista Mail Mint', 'wc-pbm'),
    description: __('Selecciona la lista de Mail Mint para obtener destinatarios suscritos.', 'wc-pbm'),
  },
};

function withCountLabel(option, value, count) {
  if (!option.value || option.value !== value || typeof count !== 'number') {
    return option;
  }

  return {
    ...option,
    label: `${option.label} (${count})`,
  };
}

export default function DependentSelector({ source, options, value, onChange, currentCount }) {
  const config = SOURCE_CONFIG[source] || SOURCE_CONFIG.product;
  const optionsWithCount = options.map((option) => withCountLabel(option, value, currentCount));

  return (
    <div className="pbm-react-selector">
      <h3>{__('Selector', 'wc-pbm')}</h3>
      <SelectControl label={config.title} value={value} options={optionsWithCount} onChange={onChange} />
      <p className="description">{config.description}</p>
    </div>
  );
}
