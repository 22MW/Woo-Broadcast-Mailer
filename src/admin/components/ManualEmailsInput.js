import { Button, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function ManualEmailsInput({ onAddManualEmails }) {
  const [value, setValue] = useState('');

  const handleAdd = () => {
    onAddManualEmails(value);
    setValue('');
  };

  return (
    <div className="pbm-react-manual-emails">
      <h3>{__('Emails manuales', 'wc-pbm')}</h3>
      <TextareaControl
        label={__('Añade emails (coma, punto y coma o salto de línea)', 'wc-pbm')}
        value={value}
        onChange={setValue}
        rows={4}
      />
      <Button variant="secondary" onClick={handleAdd} disabled={!value.trim()}>
        {__('Añadir emails manuales', 'wc-pbm')}
      </Button>
    </div>
  );
}
