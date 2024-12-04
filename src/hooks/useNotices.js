import { useDispatch } from '@wordpress/data';
import { useEffect, useRef } from '@wordpress/element';
import { store as noticesStore } from '@wordpress/notices';

export const useNotices = () => {
	const {
		createSuccessNotice: _createSuccessNotice,
		createErrorNotice: _createErrorNotice,
		createWarningNotice: _createWarningNotice,
		removeNotice,
		removeAllNotices,
	} = useDispatch(noticesStore);
	const timeoutsRef = useRef([]);

	const createNotice = async (type, ...args) => {
		const createNoticeFn = {
			error: _createErrorNotice,
			success: _createSuccessNotice,
			warning: _createWarningNotice,
		}[type];

		const data = await createNoticeFn(...args);

		if (data && data.notice.id && !data.notice.explicitDismiss) {
			timeoutsRef.current.push(
				setTimeout(() => {
					removeNotice(data.notice.id);
				}, 5000)
			); // Auto-dismiss the notice after 5 seconds
		}
	};

	const createSuccessNotice = async (...args) => {
		return createNotice('success', ...args);
	};

	const createErrorNotice = (...args) => {
		return createNotice('error', ...args);
	};

	const createWarningNotice = (...args) => {
		return createNotice('warning', ...args);
	};

	const clearNotices = () => {
		removeAllNotices('snackbar');
	};

	return {
		createSuccessNotice,
		createErrorNotice,
		createWarningNotice,
		clearNotices,
	};
};
