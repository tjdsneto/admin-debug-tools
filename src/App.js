import { Fragment } from '@wordpress/element';
import { StyleSheetManager, createGlobalStyle } from 'styled-components';
import { createHashRouter, RouterProvider } from 'react-router-dom';
import { Config } from './Pages/Config';
import { Layout } from './Common/Layout/Layout';
import { About } from './Pages/About';
import { LogViewer } from './Pages/LogViewer';

const GlobalStyle = createGlobalStyle`
	// Reset the height of the body to allow the content to fill the viewport.
	#wpcontent {
		height: calc(100vh - var(--wp-admin--admin-bar--height, 32px) - var(--wp-admin--footer--height, 40px));
		padding: 0 0 var(--wp-admin--footer--height, 40px);

		#wpbody {
			height: -webkit-fill-available;

			#wpbody-content {
				height: -webkit-fill-available;
				padding: 0;

				#adbtl-app {
					height: -webkit-fill-available;
				}
			}
		}
	}

	#wpfooter {
		// Keeps footer always visible.
		position: fixed;
	}
`;

const router = createHashRouter([
	{
		path: '/',
		element: <Layout />,
		children: [
			{
				path: '',
				element: <LogViewer />,
			},
			{
				path: 'config',
				element: <Config />,
			},
			{
				path: 'about',
				element: <About />,
			},
		],
	},
	{
		basename: '/wp-admin/tools.php?page=debug-log',
	},
]);

const App = () => {
	return (
		<Fragment>
			{/* The global style should not be namespaced/scoped. */}
			<GlobalStyle />
			<StyleSheetManager namespace="#adbtl-app">
				<RouterProvider router={router} />
			</StyleSheetManager>
		</Fragment>
	);
};

export default App;
