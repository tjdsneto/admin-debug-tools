import { SearchInput } from './Input/SearchInput';
import { SelectInput } from './Input/SelectInput';
import { levelOptions, operatorOptions } from './functions';
import { LogTypesInput } from './Input/LogTypesInput';

export function LogEntrySearchControl({ search, onChange }) {
	const handleSearchChange = (newTerm, newModifiers) => {
		onChange({
			...search,
			term: newTerm,
			modifiers: newModifiers,
		});
	};

	const handleSearchOperatorChange = (event) => {
		onChange({
			...search,
			operator: event.target.value,
		});
	};

	const handleSearchLogTypeChange = (newLogTypes) => {
		onChange({
			...search,
			logTypes: newLogTypes,
		});
	};

	return (
		<div className="flex items-center space-x-2">
			<LogTypesInput options={levelOptions} value={search.logTypes} onChange={handleSearchLogTypeChange} />
			<SelectInput options={operatorOptions} value={search.operator} onChange={handleSearchOperatorChange} />
			<SearchInput value={search} onChange={handleSearchChange} />
		</div>
	);
}
