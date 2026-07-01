import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function AudienceBuilder({ canAdd, selectedLabel, onAdd }) {
  return (
    <div className="pbm-react-audience-builder">
      <Button variant="secondary" onClick={onAdd} disabled={!canAdd}>
        {__('Añadir a lista de envío', 'wc-pbm')}
      </Button>
      {selectedLabel && <p className="description">{selectedLabel}</p>}
    </div>
  );
}
