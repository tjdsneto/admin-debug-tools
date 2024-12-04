import { useGetConfigQuery, useUpdateConfigMutation } from '../store/localApi';

export const useWpConfig = () => {
	const { data, error, isLoading } = useGetConfigQuery();
	const [updateConfig, updateConfigResult] = useUpdateConfigMutation();

	return {
		error,
		config: data?.data ?? {},
		updateConfig,
		isUpdating: updateConfigResult.isLoading,
		isLoading,
		didUpdate: updateConfigResult.isSuccess,
		didntUpdate: updateConfigResult.isError,
	};
};
