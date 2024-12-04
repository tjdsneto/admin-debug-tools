const typeClass = {
	error: 'bg-red-500',
	warning: 'bg-yellow-500',
	notice: 'bg-[#3858e9]',
	deprecation: 'bg-orange-500',
};

export function LogLineType({ line }) {
	return <span className={`${typeClass[line.type]} px-2 text-white mr-2 shrink-0`}>{line.type_label}</span>;
}
