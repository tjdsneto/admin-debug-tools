import { __ } from '@wordpress/i18n';
import { Dashicon } from '../Common/Dashicon';
import { Button } from './Button';
import { LogEntrySearchControl } from './LogEntrySearchControl';
import { Button as ButtonWp, Spinner } from '@wordpress/components';

export function LogViewerTopBar(props) {
	const { search, handleSearchChange, onAction, handleToggleFullscreen, fullscreenMode, isFetching, showLoadMore } =
		props;
	return (
		<div
			className={`flex flex-row p-2 w-full border-b border-[var(--adbtl-log-viewer-border-color)] justify-evenly`}
		>
			<div className="w-full">
				{showLoadMore ? (
					<Button className="h-full" onClick={() => onAction('loadUp')}>
						{__('Load More', 'admin-debug-tools')} &nbsp;
						{isFetching ? (
							<Spinner className="m-0" />
						) : (
							<Dashicon icon="arrow-up-alt" className="animate-bounce" size="md" />
						)}
					</Button>
				) : null}
			</div>
			<div className="flex w-full justify-center">
				<LogEntrySearchControl search={search} onChange={handleSearchChange} />
			</div>
			<div className="flex w-full gap-x-1 justify-end">
				<ButtonWp variant="primary" onClick={() => onAction('clear')}>
					{__('Clear', 'admin-debug-tools')}
				</ButtonWp>
				<ButtonWp variant="primary" onClick={() => onAction('saveAndClear')}>
					{__('Save and Clear', 'admin-debug-tools')}
				</ButtonWp>
				<ButtonWp as="a" variant="primary" onClick={() => onAction('download')}>
					{__('Download', 'admin-debug-tools')}
				</ButtonWp>
				<Button
					onClick={handleToggleFullscreen}
					title={
						fullscreenMode
							? __('Collapse from Fullscreen', 'admin-debug-tools')
							: __('Expand to Fullscreen', 'admin-debug-tools')
					}
				>
					<Dashicon icon={fullscreenMode ? 'fullscreen-exit-alt' : 'fullscreen-alt'} />
				</Button>
			</div>
		</div>
	);
}
