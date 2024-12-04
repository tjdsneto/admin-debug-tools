export const LogLineChildren = ({ children }) => {
	return (
		<div className="flex flex-col pl- my-2 w-full border border-[var(--adbtl-log-viewer-log-line-border-color)]">
			<table className="table-auto">
				<tbody>
					{children.map((child, index) => {
						let message = child.message;

						if (message.includes('{{fileLink}}')) {
							const parts = message.split('{{fileLink}}');
							message = (
								<div>
									{parts[0]}
									<a
										className="text-[var(--adbtl-log-viewer-stacktrace-link-text-color)] underline"
										href={child.stack_file_link}
										target="_blank"
										rel="noopener noreferrer"
									>
										{child.stack_file_formatted}:{child.stack_line}
									</a>
									{parts[1]}
								</div>
							);
						}

						return (
							<tr
								key={index}
								className="whitespace-pre w-full border border-[var(--adbtl-log-viewer-log-line-border-color)]"
							>
								<td className="py-[5px] px-3 w-[50px] text-right border-r mr-3 bg-[var(--adbtl-log-viewer-log-line-number-bg-color)] text-[var(--adbtl-log-viewer-log-line-number-text-color)] border-[var(--adbtl-log-viewer-log-line-border-color)]">
									<span className="inline-block leading-loose align-middle whitespace-nowrap">
										{child.line_number}
									</span>
								</td>
								<td className="py-[5px] px-[10px]">
									<div className="flex flex-col">
										{message}
										{child.stack_file_formatted &&
											!child.message.includes('{{fileLink}}') &&
											(child.stack_file_link ? (
												<a
													className="text-[var(--adbtl-log-viewer-stacktrace-link-text-color)] text-[11px] underline"
													href={child.stack_file_link}
													target="_blank"
													rel="noopener noreferrer"
												>
													{child.stack_file_formatted}:{child.stack_line}
												</a>
											) : (
												<div className="text-[var(--adbtl-log-viewer-stacktrace-link-text-color)] text-[11px]">
													{child.stack_file_formatted}:{child.stack_line}
												</div>
											))}
									</div>
								</td>
							</tr>
						);
					})}
				</tbody>
			</table>
		</div>
	);
};
