import PropTypes from 'prop-types';

export function ToggleInputBase(props) {
	const { checked, label, name, onChange } = props;

	/**
	 * The toggle switch `onChange` event handler.
	 *
	 * @param {Object} event The event object.
	 *
	 * @return {void}
	 */
	const handleChange = (event) => {
		onChange(event.target.checked, name, { event });
	};

	return (
		<label className="flex items-center cursor-pointer" htmlFor={name}>
			<span className="mr-3 text-gray-700">{label}</span>
			<div className="relative">
				<input
					type="checkbox"
					className="sr-only"
					checked={checked}
					id={name}
					name={name}
					onChange={handleChange}
				/>
				<div className="block bg-gray-300 w-14 h-8 rounded-full"></div>
				<div
					className={`dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition ${
						checked ? 'transform translate-x-6' : ''
					}`}
				></div>
			</div>
		</label>
	);
}

ToggleInputBase.propTypes = {
	checked: PropTypes.bool,
	disabled: PropTypes.bool,
	label: PropTypes.node.isRequired,
	name: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};
