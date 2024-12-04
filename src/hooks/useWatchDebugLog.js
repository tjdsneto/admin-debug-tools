import { useCallback, useRef, useState } from '@wordpress/element';
import { useSse } from './useSse';
import * as api from '../utils/apiFetch';
import * as url from '../utils/url';
import { useDebugLogAsyncUpdate } from './useDebugLogAsyncUpdate';

const initialState = {
	fileSize: null,
	lastLine: null,
	topLine: null,
	lastModified: null,
	lines: [],
	filePath: '',
};

export const useWatchDebugLog = (startOn = true) => {
	const [updateInfo, setUpdateInfo] = useState(initialState);
	const [error, setError] = useState(null);
	const [isFetching, setIsFetching] = useState(false);

	const updateInfoRef = useRef(updateInfo);
	updateInfoRef.current = updateInfo;

	const shouldUseSse = url.getParam('usesse', false);
	const sseTimeInterval = shouldUseSse ? parseInt(shouldUseSse, 10) : null;

	const onError = useCallback((err) => {
		setError(err);
	}, []);

	const mergeData = useCallback((data) => {
		// Use updateInfoRef.current to always get the latest state
		const currentUpdateInfo = updateInfoRef.current;

		if (data.file_size < currentUpdateInfo.fileSize || null === currentUpdateInfo.fileSize) {
			// If file size is smaller, it means the log was cleared.

			setUpdateInfo({
				fileSize: data.file_size,
				lastLine: data.end,
				topLine: data.start,
				lines: data.lines,
				lastModified: data.last_modified,
				filePath: data.file_path,
			});
			return;
		}

		const newUpdateInfo = { ...currentUpdateInfo };

		if (data.start === currentUpdateInfo.lastLine) {
			// If the start line is the same as the previous last line, then we should append the new lines.
			newUpdateInfo.lines = [...newUpdateInfo.lines, ...data.lines];
			newUpdateInfo.lastLine = data.end;
		} else if (data.end === currentUpdateInfo.topLine) {
			// If the end matches the top line, then we should prepend the new lines.
			newUpdateInfo.lines = [...data.lines, ...newUpdateInfo.lines];
			newUpdateInfo.topLine = data.start;
		} else {
			setUpdateInfo({
				fileSize: data.file_size,
				lastLine: data.end,
				topLine: data.start,
				lines: data.lines,
				lastModified: data.last_modified,
			});
			return;
		}

		newUpdateInfo.fileSize = data.file_size;
		newUpdateInfo.lastModified = data.last_modified;

		setUpdateInfo(newUpdateInfo);
	}, []);

	const {
		start: setSseAutoSyncOn,
		stop: setSseAutoSyncOff,
		restart: restartSseAutoSync,
		isOn: isSseAutoSyncOn,
	} = useSse(api.getSseUrl(sseTimeInterval), {
		startOn: shouldUseSse && startOn,
		onMessage: (event) => {
			if ('message' === event.type) {
				const data = JSON.parse(event.data);
				mergeData(data);
			}
		},
	});

	const refreshInterval = url.getParam('adbtl_ri');

	const {
		start: setAjaxAutoSyncOn,
		stop: setAjaxAutoSyncOff,
		restart: restartAjaxAutoSync,
		isOn: isAjaxAutoSyncOn,
	} = useDebugLogAsyncUpdate(updateInfo, {
		startOn: !shouldUseSse && startOn,
		onMessage: mergeData,
		onError,
		refreshInterval,
	});

	const fetchUpwards = async () => {
		try {
			setIsFetching(true);
			const response = await api.fetchDebugLog(updateInfo.topLine, 'upwards');
			const { data } = response;
			mergeData(data);
		} catch (err) {
			onError(err);
		} finally {
			setIsFetching(false);
		}
	};

	const isAutoSyncOn = isSseAutoSyncOn || isAjaxAutoSyncOn;

	const setAutoSyncOn = () => {
		if (shouldUseSse) {
			return setSseAutoSyncOn();
		}

		setAjaxAutoSyncOn();
	};

	const setAutoSyncOff = () => {
		if (shouldUseSse) {
			return setSseAutoSyncOff();
		}
		setAjaxAutoSyncOff();
	};

	const restartAutoSync = () => {
		if (shouldUseSse) {
			return restartSseAutoSync();
		}
		restartAjaxAutoSync();
	};

	const toggleAutoSync = () => {
		if (isAutoSyncOn) {
			setAutoSyncOff();
		} else {
			setAutoSyncOn();
		}
	};

	const clearDebugLog = async (save) => {
		await api.clearDebugLog(save);

		setUpdateInfo(initialState);

		restartAutoSync();
	};

	return { error, data: updateInfo, clearDebugLog, toggleAutoSync, isAutoSyncOn, fetchUpwards, isFetching };
};
