import { PageBody } from '../Common/Layout/PageBody';
import { Button } from '../Common/WpComponents/Button';
import { ExternalLink } from '../Common/WpComponents/ExternalLink';
import { Panel } from '../Common/WpComponents/Panel';
import { __ } from '@wordpress/i18n';

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
					<p>
						{__('For more information, visit the plugin page on', 'admin-debug-tools')}&nbsp;
						<ExternalLink
							href="https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/"
							className=""
						>
							WordPress.org
						</ExternalLink>
					</p>
				</div>
			</Panel>
			<Panel header="Leave a Review" className="!mt-4">
				<div className="p-4">
					<p className="mb-4">
						{__(
							'If you find Admin Debug Tools useful, please consider leaving a review. Your feedback helps others discover the plugin and encourages continued development.',
							'admin-debug-tools'
						)}
					</p>
					<p>
						{__('For more information, visit the plugin page on', 'admin-debug-tools')}&nbsp;
						<ExternalLink
							href="https://wordpress.org/support/plugin/admin-debug-tools/reviews?filter=5#new-post"
							className=""
						>
							{__('Leave a review on WordPress.org', 'admin-debug-tools')}
						</ExternalLink>
					</p>
				</div>
			</Panel>
			<Panel header="Feedback & Bug Reports" className="!mt-4">
				<div className="p-4">
					<p className="mb-4">
						{__(
							'Found a bug? Have a feature suggestion? We welcome your feedback and contributions!',
							'admin-debug-tools'
						)}
					</p>
					<div className="space-x-4">
						<Button
							as="a"
							variant="primary"
							__next40pxDefaultSize
							href="https://github.com/tjdsneto/admin-debug-tools/issues/new?template=bug_report.md"
							target="blank"
							rel="noopener"
						>
							{__('Report a bug', 'admin-debug-tools')}
						</Button>
						<Button
							as="a"
							variant="primary"
							__next40pxDefaultSize
							href="https://github.com/tjdsneto/admin-debug-tools/issues/new?template=feature_request.md"
							target="blank"
							rel="noopener"
						>
							{__('Suggest a feature', 'admin-debug-tools')}
						</Button>
					</div>
				</div>
			</Panel>
			<Panel header="Privacy Information" className="!mt-4">
				<div className="p-4">
					<p className="mb-4">
						{__(
							'Admin Debug Tools operates entirely within your WordPress installation. No data is sent to external servers.',
							'admin-debug-tools'
						)}
					</p>
					<p className="mb-4">
						{__(
							'The debug logs may contain sensitive information about your website. Please review them carefully before sharing with others.',
							'admin-debug-tools'
						)}
					</p>
				</div>
			</Panel>
		</PageBody>
	);
};
