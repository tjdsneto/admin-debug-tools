import { useEffect, useRef } from '@wordpress/element';

/**
 * Hook to help to keep a reference from an previous version of a value
 *
 * @param {*} value The value to keep a reference of.
 *
 * @return {*} The previous version of a value.
 */
export function usePrevious(value) {
	const ref = useRef();
	useEffect(() => {
		ref.current = value;
	});
	return ref.current;
}
