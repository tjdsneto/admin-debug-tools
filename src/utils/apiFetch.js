import { default as wpApiFetch } from '@wordpress/api-fetch';

/**
 * Fetches data from the API.
 *
 * It works as a wrapper to WordPress's apiFetch function, but adding the plugin REST API namespace to the path.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-api-fetch/
 * @see https://github.com/WordPress/gutenberg/blob/trunk/packages/api-fetch/src/index.js
 *
 * @since 1.0.0
 *
 * @param {Object}  args                 The arguments for the API fetch.
 * @param {string}  args.path            The path of the API endpoint.
 * @param {string}  [args.method='GET']  The HTTP method to use.
 * @param {Object}  args.body            The body of the request.
 * @param {boolean} [args.useBody=false] Whether to use the body as is, or to encode it as JSON.
 * @param {Object}  args.rest            The rest of the arguments.
 *
 * @return {Promise} Returns a promise that resolves to the response of the API fetch.
 */
export async function apiFetch(args) {
	const { path, method, body, useBody = false, ...rest } = args;

	const options = {};

	if (useBody) {
		// useBody is useful for when you don't want a JSON payload liek when uploading files.
		options.body = body;
	} else {
		// Adding the body as to the data property will automatically encode it as JSON and
		// add a JSON content type header.
		options.data = body;
	}

	// Catch errors and _return_ them so the RTKQ logic can track it.
	try {
		const data = await wpApiFetch({
			path: `admin-debug-tools/${path}`,
			method: method || 'GET',
			...rest,
			...options,
		}).then((response) => {
			if (response.error) {
				throw new Error(response.error);
			}
			return response;
		});
		return { data };
	} catch (error) {
		throw new Error(error?.error || 'An unexpected error occurred.');
	}
}

export const getSseUrl = (timeInterval) => {
	return window.AppData.sseUrl + '?_wpnonce=' + wpApiFetch.nonceMiddleware?.nonce + '&sseti=' + timeInterval;
};

export const getDebugLogDownloadUrl = () => {
	return window.AppData.debugLogDownloadUrl + '&_wpnonce=' + wpApiFetch.nonceMiddleware?.nonce;
};

export const getInfo = async () => {
	return await apiFetch({
		path: 'v1/index',
		method: 'GET',
	});
};

export const enableDebugLog = async () => {
	return await apiFetch({
		path: 'v1/enable',
		method: 'POST',
	});
};

export const fetchDebugLog = async (lastLine, dir = 'downwards') => {
	return await apiFetch({
		path: `v1/debug-log/updates?seek=${lastLine ?? 0}&dir=${dir}`,
		method: 'GET',
	});
};

export const clearDebugLog = async (save = false) => {
	return await apiFetch({ path: `v1/debug-log/clear?save=${save ? 1 : 0}`, method: 'POST' });
};
