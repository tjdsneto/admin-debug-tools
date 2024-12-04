import classNames from 'classnames';

export const PageBody = (props) => {
	const { className: customClassName, ...rest } = props;
	const className = classNames('my-4 mx-auto max-w-[1024px]', customClassName);

	return <div className={className} {...rest} />;
};
