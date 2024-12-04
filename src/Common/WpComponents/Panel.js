import { Panel as WpPanel } from '@wordpress/components';
import panel from './panel.module.scss';
import classNames from 'classnames';

export const Panel = (props) => {
	const { className, ...rest } = props;
	const classes = classNames(panel.panel, className);

	return <WpPanel {...rest} className={classes} />;
};
