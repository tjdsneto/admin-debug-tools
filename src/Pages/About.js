import { PageBody } from '../Common/Layout/PageBody';
import { Panel } from '../Common/WpComponents/Panel';
import { __, sprintf } from '@wordpress/i18n';

export const About = () => {
	return (
		<PageBody>
			<Panel header="About the plugin" className="">
				<div className="p-4">
					<p className="mb-4">
						{__(
							'Admin Debug Tools is a WordPress plugin that allows you to easily debug your WordPress website. It provides a simple interface to view and download the debug log file.',
							'admin-debug-tools'
						)}
					</p>
					<p className="mb-4">
						{__(
							'You can also configure the plugin to automatically sync the debug log file with the server.',
							'admin-debug-tools'
						)}
					</p>
					<p
						dangerouslySetInnerHTML={{
							__html: sprintf(
								// translators: %s: URL to the plugin page on WordPress.org
								__(
									'For more information, visit the <a href="%s">plugin page on WordPress.org</a>.',
									'admin-debug-tools'
								),
								'https://wordpress.org/plugins/admin-debug-tools/'
							),
						}}
					/>
					{/* Ask for review */}
					{/* Ask for donation */}
					{/* About bug report */}
					{/* About privacy */}
				</div>
			</Panel>
			<Panel header="About the author" className="!mt-4">
				<div className="p-4">
					<p className="mb-4">
						{__(
							'Admin Debug Tools is a WordPress plugin that allows you to easily debug your WordPress website. It provides a simple interface to view and download the debug log file.',
							'admin-debug-tools'
						)}
					</p>
					<p className="mb-4">
						{__(
							'You can also configure the plugin to automatically sync the debug log file with the server.',
							'admin-debug-tools'
						)}
					</p>
					<p
						dangerouslySetInnerHTML={{
							__html: sprintf(
								// translators: %s: URL to the plugin page on WordPress.org
								__(
									'For more information, visit the <a href="%s">plugin page on WordPress.org</a>.',
									'admin-debug-tools'
								),
								'https://wordpress.org/plugins/admin-debug-tools/'
							),
						}}
					/>
				</div>
			</Panel>
		</PageBody>
	);
};
