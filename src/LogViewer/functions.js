export const operatorOptions = [
	{ label: 'Line contains', value: 'contains' },
	{ label: 'Line starts with', value: 'startsWith' },
	{ label: 'Line ends with', value: 'endsWith' },
	{ label: 'Line equals', value: 'equals' },
];

export const levelOptions = [
	{ label: 'All', value: 'all' },
	{ label: 'Notice', value: 'notice' },
	{ label: 'Deprecated', value: 'deprecation' },
	{ label: 'Warning', value: 'warning' },
	{ label: 'Error', value: 'error' },
	{ label: 'Other', value: 'log' },
];

export const modifierOptions = [
	{ label: 'Aa', value: 'matchCase', help: 'Match Case' },
	{
		label: (
			<>
				.<sup>*</sup>
			</>
		),
		value: 'regex',
		help: 'Use Regular Expression',
	},
];

export const initialSearchState = {
	term: '',
	operator: 'contains',
	modifiers: { matchCase: false, regex: false },
	logTypes: ['all'],
};

export const createSearchRegex = (term, operator, matchCase) => {
	let pattern = term;

	switch (operator) {
		case 'startsWith':
			pattern = `^${term}`;
			break;
		case 'endsWith':
			pattern = `${term}$`;
			break;
		case 'equals':
			pattern = `^${term}$`;
			break;
		case 'contains':
		default:
			// No modification needed for 'contains'
			break;
	}

	try {
		return new RegExp(pattern, matchCase ? 'g' : 'gi');
	} catch (e) {
		return null;
	}
};

export const checkSearchMatch = (raw, term, operator, modifiers) => {
	if (!term) {
		return true;
	}

	if (modifiers.regex === 'regex') {
		try {
			const regex = createSearchRegex(term, operator, modifiers.matchCase);
			return regex.test(raw);
		} catch (e) {
			return false;
		}
	}

	if (!modifiers.matchCase) {
		raw = raw.toLowerCase();
		term = term.toLowerCase();
	}

	switch (operator) {
		case 'contains':
			return raw.includes(term);
		case 'startsWith':
			return raw.startsWith(term);
		case 'endsWith':
			return raw.endsWith(term);
		case 'equals':
			return raw === term;
		default:
			return false;
	}
};

export const collapseRepeated = (lines) => {
	return lines.reduce((accumulator, current, index) => {
		if (index === 0 || current.message !== accumulator[accumulator.length - 1]?.message) {
			accumulator.push({ ...current });
		} else {
			accumulator[accumulator.length - 1].repeated = (accumulator[accumulator.length - 1].repeated || 1) + 1;
		}
		return accumulator;
	}, []);
};
