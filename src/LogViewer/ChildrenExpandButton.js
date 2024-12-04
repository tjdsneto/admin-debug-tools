export const ChildrenExpandButton = ({ isOpen, onClick }) => {
	const classes =
		'flex before:text-xl before:font-["dashicons"] before:leading-[26px] text-[var(--adbtl-log-viewer-log-line-expand-collapse-text-color)]';

	return (
		<button onClick={onClick} className="flex items-center">
			{!isOpen ? (
				<span className={`${classes} before:content-['\f139'] before:content-['\\f139']`}></span>
			) : (
				<span className={`${classes} before:content-['\f140'] before:content-['\\f140']`}></span>
			)}
		</button>
	);
};
