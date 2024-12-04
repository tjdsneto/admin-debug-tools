import { __ } from '@wordpress/i18n';
import { getFriendlySize } from '../utils/size';

export function LogViewerBottomBar({ data }) {
	return (
		<div className="flex flex-row grow-0 p-2 w-full border-0 border-t border-solid border-[var(--adbtl-log-viewer-border-color)] justify-between">
			<div>
				{__('Log path:', 'admin-debug-tools')}&nbsp;
				<span className="italic">{data.filePath}</span>
				{/* TODO: Display Secure badge when debug.log is not publicly accessible or it's not in the default location */}
			</div>
			<div className="">
				{new Intl.NumberFormat().format(data.lastLine)} {__('lines', 'admin-debug-tools')}{' '}
				<span className="text-[var(--adbtl-log-viewer-border-color)] text-lg leading-none">|</span>{' '}
				{getFriendlySize(data.fileSize || 0)}{' '}
				<span className="text-[var(--adbtl-log-viewer-border-color)] text-lg leading-none">|</span>{' '}
				{__('Last Modified:', 'admin-debug-tools')} {data.lastModified || '-'}
			</div>
		</div>
	);
}
