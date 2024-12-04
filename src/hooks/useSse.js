import { useEffect, useRef, useState } from '@wordpress/element';

export const useSse = (url, options = {}) => {
	const [isOn, setIsOn] = useState(options.startOn);
	const { onMessage, onError, onOpen, onClose } = options;
	const evtSource = useRef(null);

	useEffect(() => {
		if (evtSource.current || !isOn) {
			return;
		}

		evtSource.current = new EventSource(url, {
			withCredentials: true,
		});

		evtSource.current.onmessage = onMessage;
		evtSource.current.onerror = onError;
		evtSource.current.onopen = onOpen;
		evtSource.current.onclose = onClose;
	}, [onClose, onError, onMessage, onOpen, url, isOn]);

	useEffect(() => {
		return () => {
			// Close the connection when the component unmounts.
			stop();
		};
	}, []);

	const stop = () => {
		if (evtSource.current) {
			evtSource.current.close();
			evtSource.current = null;
		}

		setIsOn(false);
	};

	const start = () => {
		setIsOn(true);
	};

	const restart = () => {
		if (evtSource.current) {
			evtSource.current.close();
			evtSource.current = null;
		}
	};

	return { stop, start, restart, isOn };
};
