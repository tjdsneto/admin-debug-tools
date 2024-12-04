import classNames from 'classnames';
import { Dashicon } from '../../Common/Dashicon';
import { modifierOptions } from '../functions';

export const SearchInput = ({ value, onChange }) => {
	const { name, term, modifiers } = value;

	const handleModifierChange = (modifier) => {
		const newModifiers = { ...modifiers, [modifier]: !modifiers[modifier] };
		onChange(term, newModifiers);
	};

	const handleTermChange = (event) => {
		onChange(event.target.value, modifiers);
	};

	const handleClear = () => {
		onChange('', {});
	};

	return (
		<div className="relative flex items-center">
			<input
				type="text"
				value={term}
				onChange={handleTermChange}
				placeholder="Search..."
				className="pl-2 pr-[75px] bg-[var(--adbtl-log-viewer-input-bg-color)] text-[var(--adbtl-log-viewer-input-text-color)] border-[var(--adbtl-log-viewer-input-border-color)]"
			/>
			<div className="flex gap-x-0.5 absolute right-0 mx-0.5 h-[24px]">
				{!!term && (
					<button onClick={handleClear} title="Clear Search">
						<Dashicon icon="no-alt" />
					</button>
				)}
				{modifierOptions.map((modifier) => {
					const isActive = !!modifiers[modifier.value];

					const modifierOptionClasses = classNames(
						'p-1 px-[5px] text-[11px] cursor-pointer rounded border select-none',
						{
							'font-bold bg-[var(--adbtl-log-viewer-input-active-bg-color)] text-[var(--adbtl-log-viewer-input-active-text-color)] border-[var(--adbtl-log-viewer-input-active-border-color)]':
								isActive,
							'font-normal border-[var(--adbtl-log-viewer-input-bg-color)] hover:bg-[var(--adbtl-log-viewer-input-hover-bg-color)]':
								!isActive,
						}
					);

					return (
						<label
							key={modifier.value}
							className={modifierOptionClasses}
							title={modifier.help}
							htmlFor={`${name}-${modifier.value}`}
						>
							<input
								id={`${name}-${modifier.value}`}
								type="checkbox"
								checked={modifiers[modifier.value]}
								onChange={() => handleModifierChange(modifier.value)}
								className="hidden"
							/>
							{modifier.label}
						</label>
					);
				})}
			</div>
		</div>
	);
};
