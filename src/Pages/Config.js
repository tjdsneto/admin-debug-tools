import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Panel } from '../Common/WpComponents/Panel';
import { ExternalLink } from '../Common/WpComponents/ExternalLink';
import { Button } from '../Common/WpComponents/Button';
import { FormToggle } from '../Common/WpComponents/FormToggle';
import { TextControl } from '../Common/WpComponents/TextControl';
import { useWpConfig } from '../hooks/useWpConfig';
import { StatusWrapper } from '../Common/StatusWrapper';
import { PageBody } from '../Common/Layout/PageBody';
import { Notices } from '../Common/WpComponents/Notices';
import { useNotices } from '../hooks/useNotices';
import WpConfigNotWritable from '../Common/Error/WpConfigNotWritable';

export const Config = () => {
	const { config, updateConfig, isUpdating, didUpdate, didntUpdate } = useWpConfig();
	const [configState, setConfigState] = useState(config || {});
	const [stringWpDebugLog, setStringWpDebugLog] = useState('');

	const isWPDebugLogString = typeof configState.WP_DEBUG_LOG === 'string';
	const [isWPDebugLogPathInputEnabled, setWPDebugLogPathInputEnabled] = useState(isWPDebugLogString);

	const { createSuccessNotice, createErrorNotice, createWarningNotice } = useNotices();

	useEffect(() => {
		setConfigState(config);
	}, [config]);

	const handleUpdateConfig = (key, value) => {
		if ('WP_DEBUG_LOG' === key) {
			if (typeof configState.WP_DEBUG_LOG === 'string') {
				// Backup the string value so if it's toggled back to true, we can restore it.
				setStringWpDebugLog(configState.WP_DEBUG_LOG);
			} else if (stringWpDebugLog) {
				value = stringWpDebugLog;
			}
		}

		setConfigState({
			...configState,
			[key]: value,
		});
	};

	const handleDebugLogPathCustomization = () => {
		setWPDebugLogPathInputEnabled(!isWPDebugLogPathInputEnabled);
	};

	const handleSaveChanges = async () => {
		try {
			const {
				data: { data, success, message },
			} = await updateConfig(configState).unwrap();

			setConfigState(data);
			if (success) {
				createSuccessNotice(__('Settings saved.', 'admin-debug-tools'));
			} else {
				createWarningNotice(
					sprintf(
						// translators: %s: message explaining why settings were saved with a warning.
						__(
							'Settings saved but something went wrong when testing the debug log. %s',
							'admin-debug-tools'
						),
						message
					)
				);
			}
		} catch (error) {
			let errMessage = error.message;

			if (error instanceof WpConfigNotWritable) {
				errMessage +=
					' ' +
					sprintf(
						/* translators: %s: username that needs write permission */
						__(
							'Please make sure the wp-config.php file is writable by the user `%s`.',
							'admin-debug-tools'
						),
						error.data.user
					);
			}

			createErrorNotice(__('Error on saving settings.', 'admin-debug-tools') + ' ' + errMessage);
		} finally {
		}
	};

	const configs = [
		{
			id: 'adbtl-wp-debug',
			label: 'WP_DEBUG',
			help: __('Enable WP_DEBUG mode.', 'admin-debug-tools'),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.WP_DEBUG || false}
						onChange={handleUpdateConfig.bind(null, 'WP_DEBUG', !state.WP_DEBUG)}
					/>
				);
			},
		},
		{
			id: 'adbtl-wp-debug-log',
			label: 'WP_DEBUG_LOG',
			help: __('Enable Debug logging to the wp-content/debug.log or custom file.', 'admin-debug-tools'),
			extraLabel: configState.WP_DEBUG_LOG && !isWPDebugLogString && !isWPDebugLogPathInputEnabled && (
				<div className="text-xs pl-2">
					(
					<Button variant="link" onClick={handleDebugLogPathCustomization} className="italic !text-xs">
						{__('Customize path for debug.log file', 'admin-debug-tools')}
					</Button>
					)
				</div>
			),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.WP_DEBUG_LOG || false}
						onChange={handleUpdateConfig.bind(null, 'WP_DEBUG_LOG', !state.WP_DEBUG_LOG)}
					/>
				);
			},
			bottomBody: ({ state, configField }) => {
				if (!state.WP_DEBUG_LOG || (!isWPDebugLogPathInputEnabled && !isWPDebugLogString)) {
					return;
				}

				return (
					<div className="ml-12">
						<TextControl
							className="w-full my-2"
							aria-describedby={`${configField.id}-help`}
							onChange={(value) => handleUpdateConfig('WP_DEBUG_LOG', value)}
							placeholder={__('Path to log file', 'admin-debug-tools')}
							value={true === state.WP_DEBUG_LOG ? '' : state.WP_DEBUG_LOG}
							__nextHasNoMarginBottom
						/>
						<span className="text-xs text-neutral-400">{__('Tips:', 'admin-debug-tools')}</span>
						<ul>
							<li className="text-xs text-neutral-400 mt-2">
								{__(
									'- Use the {absPath} or {wpContentDir} to set relative paths. Ex.: {wpContentDir}/logs/debug.log.',
									'admin-debug-tools'
								)}
							</li>
							<li className="text-xs text-neutral-400 mt-2">
								{__(
									'- Leave it blank to keep the debug log file in its default location (`wp-content/debug.log`).',
									'admin-debug-tools'
								)}
							</li>
						</ul>
					</div>
				);
			},
		},
		{
			id: 'adbtl-wp-debug-display',
			label: 'WP_DEBUG_DISPLAY',
			help: __('Disable display of errors and warnings.', 'admin-debug-tools'),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.WP_DEBUG_DISPLAY || false}
						onChange={handleUpdateConfig.bind(null, 'WP_DEBUG_DISPLAY', !state.WP_DEBUG_DISPLAY)}
					/>
				);
			},
		},
		{
			id: 'adbtl-wp-script-debug',
			label: 'SCRIPT_DEBUG',
			help: __(
				'Use dev versions of core JS and CSS files (only needed if you are modifying these core files or developing plugins/themes).',
				'admin-debug-tools'
			),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.SCRIPT_DEBUG || false}
						onChange={handleUpdateConfig.bind(null, 'SCRIPT_DEBUG', !state.SCRIPT_DEBUG)}
					/>
				);
			},
		},
		{
			id: 'adbtl-wp-disable-fatal-error-handler',
			label: 'WP_DISABLE_FATAL_ERROR_HANDLER',
			help: __('Enable WP_DISABLE_FATAL_ERROR_HANDLER mode.', 'admin-debug-tools'),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.WP_DISABLE_FATAL_ERROR_HANDLER || false}
						onChange={handleUpdateConfig.bind(
							null,
							'WP_DISABLE_FATAL_ERROR_HANDLER',
							!state.WP_DISABLE_FATAL_ERROR_HANDLER
						)}
					/>
				);
			},
		},
		{
			id: 'adbtl-wp-save-queries',
			label: 'SAVEQUERIES',
			help: __('Enable SAVEQUERIES mode.', 'admin-debug-tools'),
			rightBody: ({ state, configField }) => {
				return (
					<FormToggle
						id={configField.id}
						aria-describedby={`${configField.id}-help`}
						checked={state.SAVEQUERIES || false}
						onChange={handleUpdateConfig.bind(null, 'SAVEQUERIES', !state.SAVEQUERIES)}
					/>
				);
			},
			// TODO: show warning about performance impact
		},
	];

	return (
		<PageBody>
			<Notices className="mb-4" />
			<Panel header="WP Config" className="">
				<div className="p-4">
					<p className="mb-8">
						{__(
							'Configure the following WordPress constants to enable debugging features. For more information, visit: ',
							'admin-debug-tools'
						)}
						<ExternalLink
							href="https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/"
							className=""
						>
							WordPress.org
						</ExternalLink>
					</p>
					<div className="flex flex-col w-full">
						{configs.map((configField) => {
							return (
								<div key={configField.id} className="flex flex-col w-full mb-4">
									<div className="flex items-center w-full">
										<div className="w-[50px] flex">
											{configField.rightBody({ state: configState, configField })}
										</div>
										<div className="flex flex-1 flex-col">
											<div className="flex">
												<label
													htmlFor={configField.id}
													className="text-neutral-600 font-semibold mb-1"
												>
													{configField.label}
												</label>
												{configField.extraLabel}
											</div>
											<span id={`${configField.id}-help`} className="text-xs text-neutral-400">
												{configField.help}
											</span>
										</div>
									</div>
									{configField.bottomBody &&
										configField.bottomBody({ state: configState, configField })}
								</div>
							);
						})}
					</div>

					<div className="mt-8">
						<Button variant="primary" __next40pxDefaultSize onClick={handleSaveChanges}>
							<StatusWrapper isLoading={isUpdating} isSuccess={didUpdate} isError={didntUpdate}>
								{__('Save Changes', 'admin-debug-tools')}
							</StatusWrapper>
						</Button>
					</div>
				</div>
			</Panel>
		</PageBody>
	);
};
