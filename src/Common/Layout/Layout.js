import { Outlet } from 'react-router-dom';
import { Header } from './Header';

export const Layout = () => {
	return (
		<div className="h-full flex flex-col">
			<Header />
			<div className="h-full max-h-[calc(100%-60px)] relative">
				<Outlet />
			</div>
		</div>
	);
};
