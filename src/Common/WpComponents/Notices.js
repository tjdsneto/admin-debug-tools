import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticeList } from '@wordpress/components';

/**
 * @link https://developer.wordpress.org/block-editor/reference-guides/data/data-core-notices
 */
export const Notices = ({ className }) => {
	const { removeNotice } = useDispatch(noticesStore);
	const notices = useSelect((select) => select(noticesStore).getNotices());
	const defaultNotices = notices.filter((notice) => notice.type === 'default');

	if (defaultNotices.length === 0) {
		return null;
	}

	const parsedNotices = defaultNotices.map((notice) => {
		return {
			...notice,
			className: 'mb-4',
		};
	});

	return <NoticeList className={className} notices={parsedNotices} onRemove={removeNotice} />;
};
