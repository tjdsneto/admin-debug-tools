import classNames from 'classnames';

const sizes = {
	sm: '',
	md: 'text-base leading-none w-4 h-4',
	lg: '',
};

export const Dashicon = ({ icon, size, className }) => {
	const classes = classNames(className, `dashicons dashicons-${icon}`, {
		[sizes[size]]: sizes[size],
	});

	return <span className={classes} />;
};
