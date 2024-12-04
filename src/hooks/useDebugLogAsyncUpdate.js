import { useEffect, useRef, useState } from '@wordpress/element';
import * as api from '../utils/apiFetch';

export const useDebugLogAsyncUpdate = (updateInfo, options = {}) => {
	const [isOn, setIsOn] = useState(options.startOn);
	const { onMessage, onError } = options;
	const timeoutId = useRef(null);
	const restartFlag = useRef(null);
	const isFetchingRef = useRef(null);
	const seqErrorCntRef = useRef(0);
	const lastLineRef = useRef(updateInfo.lastLine);
	const refreshInterval = (options.refreshInterval || 15) * 1000;

	useEffect(() => {
		if (!isOn || isFetchingRef.current) {
			return;
		}

		const recurringFetchDebugLog = () => {
			if (seqErrorCntRef.current >= 10) {
				// Stop fetching if there are 10 consecutive errors.
				stop();
				return;
			}

			isFetchingRef.current = true;
			api.fetchDebugLog(lastLineRef.current)
				.then((response) => {
					const { data } = response;
					onMessage(data);
					lastLineRef.current = data.end;
					seqErrorCntRef.current = 0;
				})
				.catch((error) => {
					// eslint-disable-next-line no-console
					console.error('Error fetching debug log:', error);
					seqErrorCntRef.current += 1;
					onError(error);
				})
				.finally(() => {
					// Call fetchDebugLog again after 15 seconds
					timeoutId.current = setTimeout(recurringFetchDebugLog, refreshInterval);
					isFetchingRef.current = false;
				});
		};

		timeoutId.current = recurringFetchDebugLog();

		// Cleanup function
		// return () => {
		// 	clearTimeout( timeoutId );
		// };
	}, [isOn, onMessage]);

	useEffect(() => {
		if (!isOn && restartFlag.current) {
			restartFlag.current = false;
			start();
		}
	}, [isOn]);

	useEffect(() => {
		return () => {
			// Close the connection when the component unmounts.
			stop();
		};
	}, []);

	const stop = () => {
		if (timeoutId.current) {
			clearTimeout(timeoutId.current);
			timeoutId.current = null;
		}

		setIsOn(false);
	};

	const start = () => {
		setIsOn(true);
	};

	const restart = () => {
		stop();
		lastLineRef.current = 0;
		restartFlag.current = true;
	};

	return { stop, start, restart, isOn };
};
