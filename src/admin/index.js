import { createRoot } from '@wordpress/element';
import App from './App';
import EmailStringEditorApp from './email-editor/EmailStringEditorApp';
import './styles.css';

const mountNode = document.getElementById('pbm-admin-app');
const emailEditorMountNode = document.getElementById('pbm-email-string-editor-app');

if (mountNode) {
  const root = createRoot(mountNode);
  root.render(<App />);
}

if (emailEditorMountNode) {
  const root = createRoot(emailEditorMountNode);
  root.render(<EmailStringEditorApp />);
}
