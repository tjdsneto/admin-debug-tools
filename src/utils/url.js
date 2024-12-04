export const hasParam = (param) => {
	const queryParams = new URLSearchParams(location.search);
	return queryParams.has(param);
};

export const getParam = (param, defaultValue) => {
	const queryParams = new URLSearchParams(location.search);
	return queryParams.has(param) || typeof defaultValue === 'undefined' ? queryParams.get(param) : defaultValue;
};
