/*global wcSettings, wpw_currency_switcher*/

const getLabel = () => {
	/** @namespace wpw_currency_switcher.i18n.Currency */
	return wpw_currency_switcher.i18n.Currency;
};

const getStoreCurrency = () => {
	return wcSettings.currency.code;
};

const getCurrencies = () => {
	/** @namespace wpw_currency_switcher.currencies */
	return wpw_currency_switcher.currencies;
};

/**
 * List of wc-analytics pages where currency is used.
 *
 * @return {string[]} The list of pages.
 */
const getPages = () => {
	return [
		"orders",
		"revenue",
		"products",
		"categories",
		"coupons",
		"taxes",
	];
};

module.exports = {
	getLabel,
	getCurrencies,
	getPages,
	getStoreCurrency
};

