import { Tooltip as WpTooltip } from '@wordpress/components';

export const Tooltip = (props) => {
	return <WpTooltip portal={false} {...props} />;
};
