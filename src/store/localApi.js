import { createApi } from '@reduxjs/toolkit/query/react';
import { apiFetch } from '../utils/apiFetch';

/**
 * A custom base query function for use with RTK Query.
 *
 * This custom base query uses the `apiFetch` function from the WordPress, which
 * knows how to handle the WP API and authentication.
 *
 * @see https://redux-toolkit.js.org/rtk-query/usage/customizing-queries#implementing-a-custom-basequery
 *
 * @since 1.0.0
 *
 * @param {Object} args The arguments for the AJAX call.
 *
 * @return {Object} An object with the data or error from the AJAX call.
 */
const customBaseQuery = async (args) => {
	// Catch errors and _return_ them so the RTKQ logic can track it.
	try {
		const data = await apiFetch(args).then((response) => {
			if (response.error) {
				throw new Error(response.error);
			}
			return response;
		});
		return { data };
	} catch (error) {
		return { error };
	}
};

export const localApi = createApi({
	baseQuery: customBaseQuery,
	tagTypes: ['Config'],
	endpoints: (builder) => ({
		/**
		 * A query function for fetching the WP Config.
		 *
		 * @param {Object} args The arguments for the AJAX call.
		 *
		 * @return {Object} An object with the path for the AJAX call.
		 */
		getConfig: builder.query({
			query: () => {
				return {
					path: `v1/wp-config`,
				};
			},

			// Cache tags to autoamte re-fetching
			// See: https://redux-toolkit.js.org/rtk-query/usage/automated-refetching
			providesTags: ['Config'],
		}),

		/**
		 * A mutation function to update the WP Config.
		 *
		 * @param {Object} args The arguments for the AJAX call.
		 *
		 * @return {Object} An object with the path, method, and body for the AJAX call.
		 */
		updateConfig: builder.mutation({
			query: (config) => {
				return {
					path: `v1/wp-config`,
					method: 'POST',
					body: {
						...config,
					},
				};
			},

			onQueryStarted: async (config, { dispatch, queryFulfilled }) => {
				try {
					const {
						data: { data },
					} = await queryFulfilled;
					dispatch(
						localApi.util.updateQueryData('getConfig', undefined, (draft) => {
							Object.assign(draft, data);
						})
					);
				} catch (error) {
					console.error('Failed to update cache for Admin Debug Tools Config:', error);
				}
			},
		}),
	}),
});

export const { useGetConfigQuery, useUpdateConfigMutation } = localApi;
