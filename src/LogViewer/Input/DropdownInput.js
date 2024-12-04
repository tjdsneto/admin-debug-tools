import { useEffect, useRef, useState } from '@wordpress/element';
import classNames from 'classnames';

export function DropdownInput(props) {
	const { name, options, value = [], onChange, label } = props;
	const [isOpen, setIsOpen] = useState(false);
	const dropdownRef = useRef(null);

	const toggleDropdown = () => {
		setIsOpen(!isOpen);
	};

	const handleCheckboxChange = (optionValue) => {
		const newSelectedOptions = value.includes(optionValue)
			? value.filter((val) => val !== optionValue)
			: [...value, optionValue];

		onChange(newSelectedOptions, optionValue);
	};

	const handleClickOutside = (event) => {
		if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
			setIsOpen(false);
		}
	};

	useEffect(() => {
		document.addEventListener('mousedown', handleClickOutside);
		return () => {
			document.removeEventListener('mousedown', handleClickOutside);
		};
	}, []);

	const buttonClasses = classNames(
		'block appearance-none w-full rounded p-1 px-2 h-[30px] text-left border bg-[var(--adbtl-log-viewer-input-bg-color)] hover:text-[var(--adbtl-log-viewer-input-text-color)] focus:text-[var(--adbtl-log-viewer-input-text-color)] text-[var(--adbtl-log-viewer-input-text-color)] border-[var(--adbtl-log-viewer-input-border-color)] cursor-pointer',
		{
			'border-[#2271b1] shadow-[0_0_0_1px_#2271b1] outline-2 outline-transparent': isOpen, // Same :active styles from WP forms.css
		}
	);

	return (
		<div ref={dropdownRef} className="relative w-full min-w-[130px]">
			<button className={buttonClasses} onClick={toggleDropdown}>
				{label}
			</button>
			{isOpen && (
				<div className="absolute z-10 mt-1 w-full rounded bg-[var(--adbtl-log-viewer-input-bg-color)] border border-[var(--adbtl-log-viewer-input-border-color)] shadow-lg">
					{options.map((option) => (
						<label
							key={option.value}
							className="block px-2 py-2 cursor-pointer hover:bg-[var(--adbtl-log-viewer-input-hover-bg-color)]"
							htmlFor={`${name}-${option.value}`}
						>
							<input
								id={`${name}-${option.value}`}
								type="checkbox"
								checked={value.includes(option.value)}
								onChange={() => handleCheckboxChange(option.value)}
								className="mr-2"
							/>
							{option.label}
						</label>
					))}
				</div>
			)}
			<div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[var(--adbtl-log-viewer-input-text-color)]">
				<svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
					<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
				</svg>
			</div>
		</div>
	);
}
