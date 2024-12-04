export function SelectInput(props) {
	const { options, value, onChange } = props;

	return (
		<div className="relative w-full min-w-[165px]">
			<select
				className="block appearance-none w-full rounded p-1 px-2 border bg-[var(--adbtl-log-viewer-input-bg-color)] hover:text-[var(--adbtl-log-viewer-input-text-color)] focus:text-[var(--adbtl-log-viewer-input-text-color)] text-[var(--adbtl-log-viewer-input-text-color)] border-[var(--adbtl-log-viewer-input-border-color)]"
				value={value}
				onChange={onChange}
			>
				{options.map((option) => (
					<option key={option.value} value={option.value}>
						{option.label}
					</option>
				))}
			</select>
			<div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[var(--adbtl-log-viewer-input-text-color)]">
				<svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
					<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
				</svg>
			</div>
		</div>
	);
}
