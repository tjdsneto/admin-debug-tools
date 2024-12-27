import { useState } from '@wordpress/element';
import classNames from 'classnames';
import { LogViewerTopBar } from './LogViewerTopBar';
import { LogViewerBottomBar } from './LogViewerBottomBar';
import { LogViewerEntries } from './LogViewerEntries';
import { usePrevious } from '../hooks/usePrevious';
import { checkSearchMatch, collapseRepeated, initialSearchState } from './functions';
import './themes.scss';

export const LogViewer = ({ data, onAction, theme: themeSlug, isFetching }) => {
	const { lines } = data;
	const [search, setSearch] = useState(initialSearchState);
	const [fullscreenMode, setFullscreenMode] = useState(false);
	const [isScrollOnTop, setIsScrollOnStop] = useState(false);
	const lastLine = lines[lines.length - 1]?.line_number;
	const prevLastLine = usePrevious(lastLine);

	const fetchStatus = {
		isFetching,
		hasUpdated: lastLine !== prevLastLine,
		isFirstFetch: lines && !prevLastLine,
	};

	const handleSearchChange = (newSearch) => {
		setSearch({ ...search, ...newSearch });
	};

	const handleToggleFullscreen = () => {
		setFullscreenMode(!fullscreenMode);
	};

	const filteredLines = collapseRepeated(
		lines.filter((line) => {
			if (!search.logTypes.includes('all') && !search.logTypes.includes(line.type)) {
				return false;
			}

			return (
				checkSearchMatch(line.raw, search.term, search.operator, search.modifiers) ||
				line.children?.some((child) =>
					checkSearchMatch(child.raw, search.term, search.operator, search.modifiers)
				)
			);
		})
	);

	const wrapperClasses = classNames(
		'rounded font-mono flex flex-col grow transition-all shadow-md overflow-hidden',
		`adbtl-log-viewer adbtl-log-viewer-theme--${themeSlug}`,
		'bg-[var(--adbtl-log-viewer-bg-color)] text-[var(--adbtl-log-viewer-text-color)]',
		{
			'm-0 fixed top-0 left-0 w-full h-full z-[999999]': fullscreenMode,
			'': !fullscreenMode,
		}
	);

	const handleScrollOnTop = (newIsScrollOnTop) => {
		setIsScrollOnStop(newIsScrollOnTop);
	};

	return (
		<div className={wrapperClasses}>
			<LogViewerTopBar
				search={search}
				handleSearchChange={handleSearchChange}
				onAction={onAction}
				handleToggleFullscreen={handleToggleFullscreen}
				fullscreenMode={fullscreenMode}
				isFetching={isFetching}
				showLoadMore={isScrollOnTop && lines.length && lines[0].line_number > 1}
			/>
			<LogViewerEntries
				entries={filteredLines}
				onAction={onAction}
				showLoadMore={isScrollOnTop && lines.length && lines[0].line_number > 1}
				onScrollOnTop={handleScrollOnTop}
				fetchStatus={fetchStatus}
			/>
			<LogViewerBottomBar data={data} />
		</div>
	);
};
