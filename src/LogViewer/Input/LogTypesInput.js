import { DropdownInput } from './DropdownInput';

export function LogTypesInput(props) {
	const { value } = props;

	let label = 'Custom types';

	if (value.includes('all')) {
		label = 'All types';
	}

	if (!value.length) {
		label = 'Hide all';
	}

	const handleChange = (newLogTypes, optionValue) => {
		if ('all' === optionValue && newLogTypes.includes('all')) {
			newLogTypes = props.options.map((option) => option.value);
		} else if ('all' === optionValue && !newLogTypes.includes('all')) {
			newLogTypes = [];
		} else if (newLogTypes.includes('all')) {
			newLogTypes = newLogTypes.filter((val) => val !== 'all');
		}

		props.onChange(newLogTypes);
	};

	return <DropdownInput label={label} value={value} {...props} onChange={handleChange} />;
}
