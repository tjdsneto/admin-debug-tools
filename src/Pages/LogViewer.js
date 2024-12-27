import { LogViewer as LogViewerComp } from '../LogViewer/LogViewer';
import { useWatchDebugLog } from '../hooks/useWatchDebugLog';
import { FormToggle } from '../Common/WpComponents/FormToggle';
import { __ } from '@wordpress/i18n';
import styled from 'styled-components';
import { useWpConfig } from '../hooks/useWpConfig';
import { useEffect, useState } from '@wordpress/element';
import { getDebugLogDownloadUrl } from '../utils/apiFetch';
import { useNotices } from '../hooks/useNotices';
import { usePrevious } from '../hooks/usePrevious';
import { SnackbarList } from '../Common/WpComponents/SnackbarList';
import { Dashicon } from '../Common/Dashicon';
import { Tooltip } from '../Common/WpComponents/Tooltip';

const StyledFormToggle = styled(FormToggle)`
	--wp-admin-theme-color: #10b981;
`;

export const LogViewer = () => {
	const { error: configError, config, updateConfig, isUpdating } = useWpConfig();
	const { data, toggleAutoSync, clearDebugLog, isAutoSyncOn, error: watchError, isFetching, fetchUpwards } = useWatchDebugLog(true);
	const { createErrorNotice } = useNotices();

	// TODO: Implement theme selection
	const [theme] = useState('dark');

	const prevConfigError = usePrevious(configError);
	const prevWatchError = usePrevious(watchError);

	useEffect(() => {
		if (configError && prevConfigError !== configError) {
			createErrorNotice(__('Error fetching WP config.', 'admin-debug-tools'), {
				type: 'snackbar',
				icon: <Dashicon icon="no" />,
				explicitDismiss: true,
			});
		}
	}, [configError, createErrorNotice, prevConfigError]);

	useEffect(() => {
		if (watchError && prevWatchError !== watchError) {
			createErrorNotice(__('Error fetching updates from WP debug log file.', 'admin-debug-tools'), {
				type: 'snackbar',
				icon: <Dashicon icon="no" />,
				explicitDismiss: true,
			});
		}
	}, [watchError, createErrorNotice, prevWatchError]);

	const isDebugLog = config.WP_DEBUG && config.WP_DEBUG_LOG;

	const handleAction = (action) => {
		switch (action) {
			case 'saveAndClear':
				return clearDebugLog(true);
			case 'clear':
				return clearDebugLog();
			case 'toggleAutoSync':
				return toggleAutoSync();
			case 'download':
				return downloadDebugLog();
			case 'loadUp':
				return fetchUpwards();
			default:
		}
	};

	const downloadDebugLog = () => {
		// Create a temporary anchor element
		const a = document.createElement('a');
		a.href = getDebugLogDownloadUrl();
		a.download = ''; // Optional: specify a default filename
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
	};

	// const handleThemeSelection = (event) => {
	// 	setTheme(event.target.value);
	// };

	const handleToggleLogging = async () => {
		try {
			await updateConfig({
				WP_DEBUG: !isDebugLog,
				WP_DEBUG_LOG: config.WP_DEBUG_LOG || !isDebugLog,
			}).unwrap();

			if ((isDebugLog && isAutoSyncOn) || (!isDebugLog && !isAutoSyncOn)) {
				toggleAutoSync();
			}
		} catch (error) {
			createErrorNotice(__('Error toggling WP logging.', 'admin-debug-tools') + ` ${error.message}`, {
				type: 'snackbar',
				icon: <Dashicon icon="no" />,
				explicitDismiss: true,
			});
		} finally {
		}
	};

	return (
		<div className="p-4 h-full flex flex-col max-h-full overflow-hidden">
			<div className="flex flex-row w-full justify-between">
				<div className="flex gap-x-4 justify-start items-center">
					{/* Future feature: Theme selection
					<select name="theme" onChange={handleThemeSelection} value={theme}>
						<option value="dark">Dark</option>
						<option value="light">Light</option>
					</select> */}
				</div>
				<div className={`flex gap-x-4 justify-end items-center h-[50px] ${isUpdating && 'opacity-50'}`}>
					<Tooltip text={__('Enable or disable logging', 'admin-debug-tools')}>
						<label className="flex gap-x-2" htmlFor="wp-logging-toggle">
							{__('WP Logging Enabled:', 'admin-debug-tools')}{' '}
							<StyledFormToggle
								id="wp-logging-toggle"
								checked={isDebugLog || false}
								onChange={handleToggleLogging}
							/>
						</label>
					</Tooltip>
					<Tooltip text={__('Watch for updates automatically', 'admin-debug-tools')}>
						<label className="flex gap-x-2" htmlFor="auto-sync-toggle">
							{__('Auto Sync:', 'admin-debug-tools')}{' '}
							<StyledFormToggle
								id="auto-sync-toggle"
								checked={isAutoSyncOn || false}
								onChange={toggleAutoSync}
							/>
						</label>
					</Tooltip>
				</div>
			</div>
			<LogViewerComp data={data} onAction={handleAction} theme={theme} isFetching={isFetching} />
			<SnackbarList />
		</div>
	);
};
