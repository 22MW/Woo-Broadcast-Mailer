import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function SourceSelector({ sources, source, onChange }) {
  return (
    <div className="pbm-react-source">
      <h3>{__('Fuente', 'wc-pbm')}</h3>
      <div className="pbm-react-source-buttons">
        {sources.map((item) => (
          <Button
            key={item.value}
            variant={source === item.value ? 'primary' : 'secondary'}
            className="pbm-react-source-btn"
            disabled={item.disabled}
            onClick={() => onChange(item.value)}
          >
            {item.label}
          </Button>
        ))}
      </div>
    </div>
  );
}
