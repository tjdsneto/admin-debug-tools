import { ExternalLink as WpExternalLink } from '@wordpress/components';
import styles from './ExternalLink.module.scss';
import classNames from 'classnames';

export const ExternalLink = (props) => {
	const { className, ...rest } = props;
	const classes = classNames(styles.ExternalLink, className);

	return <WpExternalLink {...rest} className={classes} />;
};
