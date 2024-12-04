import { TabPanel } from '@wordpress/components';
import { useLocation, useNavigate } from 'react-router-dom';
import { Logo } from './Logo';
import { useNotices } from '../../hooks/useNotices';

export const Header = () => {
	const navigate = useNavigate();
	const location = useLocation();
	const { clearNotices } = useNotices();

	const currentRoute = location.pathname === '/' ? 'logViewer' : location.pathname.replace('/', '');

	const handleSelectTab = (tab) => {
		// Clear notices when switching tabs so that the user doesn't see notices from the previous tab.
		clearNotices();

		navigate('logViewer' === tab ? '/' : tab);
	};

	return (
		<div className="bg-white w-full h-[64px] flex items-center pl-4 border-t-4 border-[var(--wp-admin-theme-color,#3858e9)]">
			<div className="flex mr-4 items-center font-bold">
				<Logo className="w-8 h-8 mr-2" />
				Admin Debug Tools
			</div>
			<nav>
				<TabPanel
					onSelect={handleSelectTab}
					initialTabName={currentRoute}
					tabs={[
						{
							name: 'logViewer',
							title: 'Debug Log',
							className: '!h-[60px]',
						},
						{
							name: 'config',
							title: 'WP Config',
							className: '!h-[60px]',
						},
						{
							name: 'about',
							title: 'About',
							className: '!h-[60px]',
						},
					]}
				>
					{() => {}}
				</TabPanel>
			</nav>
		</div>
	);
};
