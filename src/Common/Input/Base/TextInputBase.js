import PropTypes from 'prop-types';
import classnames from 'classnames';

export function TextInputBase(props) {
	const { className, label, name, onChange, value, wrapperClasses, labelClasses, inputClasses, ...rest } = props;

	/**
	 * The `onChange` input callback.
	 *
	 * @param {Object} event The `onChange` event object.
	 *
	 * @return {void}
	 */
	const handleChange = (event) => {
		onChange(event.target.value, name, { event });
	};

	const wrapperClassName = classnames('', wrapperClasses);
	const labelClassName = classnames('', labelClasses);
	const inputClassName = classnames('', inputClasses);

	return (
		<label className={wrapperClassName} htmlFor={name}>
			<span className={labelClassName}>{label}</span>
			<input
				className={inputClassName}
				id={name}
				name={name}
				onChange={handleChange}
				value={value}
				type="text"
				{...rest}
			/>
		</label>
	);
}

TextInputBase.propTypes = {
	className: PropTypes.string,
	wrapperClasses: PropTypes.string,
	labelClasses: PropTypes.string,
	inputClasses: PropTypes.string,
	disabled: PropTypes.bool,
	label: PropTypes.node.isRequired,
	name: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
	placeholder: PropTypes.string,
	type: PropTypes.string,
	value: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
};
