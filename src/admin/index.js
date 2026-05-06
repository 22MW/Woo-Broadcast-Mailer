import { createRoot } from '@wordpress/element';
import App from './App';
import './styles.css';

const mountNode = document.getElementById('pbm-admin-app');

if (mountNode) {
  const root = createRoot(mountNode);
  root.render(<App />);
}
