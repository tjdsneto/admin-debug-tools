import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { LogLine } from './LogLine';

import { createUseGesture, dragAction, wheelAction } from '@use-gesture/react';

import { useSpring, animated } from '@react-spring/web';
import { usePrevious } from '../hooks/usePrevious';

export function LogViewerEntries({ entries, isFetching, onScrollOnTop, showLoadMore, onAction }) {
	const scrollRef = useRef(null);
	const scrollMuch = useRef(0);
	const divRef = useRef();
	const [isScrollOnTop, setIsScrollOnStop] = useState(false);
	const wasFetching = usePrevious(isFetching);

	const useGesture = createUseGesture([dragAction, wheelAction]);

	const [springProps, setSpring] = useSpring(() => ({
		transform: 'translateY(0px)',
		config: {
			mass: 0.0001,
			tension: 10,
			friction: 5,
			bounds: { top: 0 },
			rubberband: false,
			clamp: true,
		},
	}));

	const [opacitySpring, setOpacitySpring] = useSpring(() => ({
		opacity: 0,
		top: 0,
		height: 0,
		config: { mass: 1, tension: 0, friction: 0, clamp: true },
	}));

	useEffect(() => {
		scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
		const newIsScrollOnTop = scrollRef.current.scrollTop === 0;
		setIsScrollOnStop(newIsScrollOnTop);
		onScrollOnTop(newIsScrollOnTop);
	}, [onScrollOnTop]);

	useEffect(() => {
		if (!isFetching && wasFetching) {
			setSpring({ transform: 'translateY(0px)' });
			setOpacitySpring({
				opacity: 0,
				top: 0,
				height: 0,
			});
		}
	}, [isFetching, wasFetching, setSpring, setOpacitySpring]);

	const handleScrollDrag = (state) => {
		if (!showLoadMore) {
			return;
		}

		if (!isScrollOnTop) {
			return;
		}

		if (isFetching) {
			return;
		}

		if (state.first || state.last || state.movement[1] === 0) {
			scrollMuch.current = 0;
			setSpring({ transform: 'translateY(0px)' });
			setOpacitySpring({
				opacity: 0,
				top: 0,
				height: 0,
			});
			return;
		}

		const offset = 0;
		const maxScroll = 100;
		let sensitivity = 0.1;

		if (state.dragging) {
			sensitivity = 0.5;
			const dragDelta = -state.movement[1]; // Negative because drag direction is opposite to scroll
			scrollMuch.current = dragDelta * sensitivity;
		} else if (state.wheeling) {
			sensitivity = 0.1;
			const deltaY = state.delta[1];
			scrollMuch.current += deltaY * sensitivity;
		}

		let translateY = scrollMuch.current < offset ? -1 * (scrollMuch.current - offset) : 0;
		translateY = Math.min(translateY, maxScroll);

		if (maxScroll === translateY) {
			onAction('loadUp');
			state.cancel();
		}

		setSpring({
			transform: `translateY(${translateY}px)`,
		});

		setOpacitySpring({
			opacity: translateY / maxScroll,
			top: -100 * (translateY / maxScroll),
			height: 100 * (translateY / maxScroll),
		});
	};

	const scrollGestures = useGesture({
		onWheel: handleScrollDrag,
		onDrag: handleScrollDrag,
	});

	const scrollOnTopGesture = useGesture({
		onWheel: () => {
			const newIsScrollOnTop = scrollRef.current.scrollTop === 0;
			if (newIsScrollOnTop !== isScrollOnTop) {
				setIsScrollOnStop(newIsScrollOnTop);

				// Inform the parent component that the scroll is on top. The Load More button only should only be shown when the scroll is on top.
				onScrollOnTop(newIsScrollOnTop);
			}
		},
	});

	return (
		<div className="overflow-x-auto overflow-y-hidden grow flex">
			<animated.div
				className="overflow-x-visible flex grow transition ease-in-out relative max-w-full"
				{...scrollGestures()}
				ref={divRef}
				style={springProps}
			>
				<animated.div
					className="absolute top-[-20px] flex justify-center items-center w-full"
					style={opacitySpring}
				>
					{isFetching ? __('Loading…', 'admin-debug-tools') : __('Load More', 'admin-debug-tools')}
				</animated.div>
				<div className="overflow-y-auto overflow-x-visible grow" ref={scrollRef} {...scrollOnTopGesture()}>
					<table className="table-auto h-full w-full">
						<tbody>
							{entries.map((line, index) => {
								return <LogLine key={index} line={line} />;
							})}

							{/* Empty row to take the rest of the available vertical space */}
							<tr className="h-full">
								<td className=" min-w-[50px] w-[50px] border-r bg-[var(--adbtl-log-viewer-log-line-number-bg-color)] border-[var(--adbtl-log-viewer-log-line-border-color)]">
									&nbsp;
								</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			</animated.div>
		</div>
	);
}
