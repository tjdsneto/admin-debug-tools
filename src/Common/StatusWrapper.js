import { Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { Icon, check, close } from '@wordpress/icons';

export const StatusWrapper = (props) => {
	const { isLoading, isSuccess, isError, children } = props;
	const [showCheckmark, setShowCheckmark] = useState(false);

	useEffect(() => {
		if (isSuccess) {
			setShowCheckmark(true);
			const timer = setTimeout(() => {
				setShowCheckmark(false);
			}, 3000); // Hide checkmark after 5 seconds

			return () => clearTimeout(timer); // Cleanup the timer on component unmount
		}
	}, [isSuccess]);

	if (isLoading) {
		return (
			<>
				<Spinner /> {children}
			</>
		);
	}

	if (showCheckmark) {
		return (
			<>
				<Icon icon={check} /> {children}
			</>
		);
	}

	if (isError) {
		return (
			<>
				<Icon icon={close} /> {children}
			</>
		);
	}

	return children;
};
