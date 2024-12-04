import { useState } from '@wordpress/element';
import { ChildrenExpandButton } from './ChildrenExpandButton';
import { RepeatedLineBadge } from './LogLineRepeatedLineBadge';
import { LogLineChildren } from './LogLineChildren';
import { LogLineType } from './LogLineType';

export const LogLine = ({ line }) => {
	const [isOpen, setIsOpen] = useState(false);

	const toggleOpen = () => {
		setIsOpen(!isOpen);
	};

	const hasChildren = line.children && line.children.length > 0;

	const hasRepeated = line.repeated && line.repeated > 1;

	const hasType = line.type && line.type !== 'info' && line.type_label;

	let message = line.message;

	if (message.includes('{{fileLink}}')) {
		const parts = message.split('{{fileLink}}');
		message = (
			<div>
				{parts[0]}
				<a
					className="text-[var(--adbtl-log-viewer-stacktrace-link-text-color)] underline"
					href={line.stack_file_link}
					target="_blank"
					rel="noopener noreferrer"
				>
					{line.stack_file_formatted}:{line.stack_line}
				</a>
				{parts[1]}
			</div>
		);
	}

	return (
		<tr className="max-h-min-content hover:bg-[var(--adbtl-log-viewer-log-line-hover-bg-color)] border-solid border border-[var(--adbtl-log-viewer-log-line-border-color)]">
			<td className="text-right py-[5px] px-2 border-r min-w-[50px] w-[50px] bg-[var(--adbtl-log-viewer-log-line-number-bg-color)] text-[var(--adbtl-log-viewer-log-line-number-text-color)] border-[var(--adbtl-log-viewer-log-line-border-color)]">
				<span className="inline-block leading-loose align-middle whitespace-nowrap">{line.line_number}</span>
			</td>

			<td className="py-[5px] px-2 min-w-[150px] w-[150px] text-[var(--adbtl-log-viewer-log-line-date-text-color)]">
				<span className="inline-block leading-loose align-middle whitespace-nowrap">
					{line.datetime_formatted}
				</span>
			</td>

			<td className="p-[5px] text-center">
				{/* Show expand/collapse button if there is children */}
				{hasChildren && <ChildrenExpandButton isOpen={isOpen} onClick={toggleOpen} />}

				{/* Show repeated badge if there is repeated line - but only if not already showing the expand/collapse button */}
				{hasRepeated && !hasChildren && (
					<div className="leading-loose align-middle">
						<RepeatedLineBadge count={line.repeated} />
					</div>
				)}
			</td>

			<td className="py-[5px] px-2">
				<div className="flex items-center leading-loose whitespace-nowrap">
					{hasType ? <LogLineType line={line} /> : null}
					{message}
				</div>
				{hasChildren && isOpen ? <LogLineChildren children={line.children} /> : null}
			</td>
		</tr>
	);
};
