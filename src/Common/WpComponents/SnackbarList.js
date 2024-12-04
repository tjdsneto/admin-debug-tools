import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { SnackbarList as SnackbarListComp } from '@wordpress/components';
import classNames from 'classnames';

/**
 * @link https://developer.wordpress.org/block-editor/reference-guides/data/data-core-notices
 */
export const SnackbarList = ({ className }) => {
	const { removeNotice } = useDispatch(noticesStore);
	const notices = useSelect((select) => select(noticesStore).getNotices());
	const snackbarNotices = notices.filter((notice) => notice.type === 'snackbar');

	if (snackbarNotices.length === 0) {
		return null;
	}

	const parsedNotices = snackbarNotices.map((notice) => {
		const classes = classNames('mb-4', `components-snackbar--${notice.status}`);

		return {
			...notice,
			className: classes,
		};
	});

	return <SnackbarListComp className={className} notices={parsedNotices} onRemove={removeNotice} />;
};
