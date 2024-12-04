import { createRoot, render } from '@wordpress/element';
import { Provider } from 'react-redux';
import App from './App';
import { store } from './rootStore';
import { appId } from './data';

/**
 * Import the stylesheet for the plugin.
 */
import './style.scss';
import '../node_modules/@wordpress/components/build-style/style.css';

const el = document.getElementById(appId);

const app = (
	<Provider store={store}>
		<App />
	</Provider>
);

if (createRoot) {
	// Render the App component into the DOM
	createRoot(el).render(app);
} else {
	render(app, el);
}
