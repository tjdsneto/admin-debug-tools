class WpConfigNotWritable extends Error {
	constructor(message, data) {
		super(message);
		this.name = 'WpConfigNotWritable';
		this.data = data;
	}
}

export default WpConfigNotWritable;
