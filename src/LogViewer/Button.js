import classNames from 'classnames';

export const Button = ({ children, onClick, className, ...props }) => {
	return (
		<button
			onClick={onClick}
			className={classNames(
				'text-white border px-2 py-1 rounded cursor-pointer border-[var(--adbtl-log-viewer-button-border-color)] hover:border-[var(--adbtl-log-viewer-button-hover-border-color)] flex justify-center items-center',
				className
			)}
			{...props}
		>
			{children}
		</button>
	);
};
