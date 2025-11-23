// src/composables/useCurrency.js
export function useCurrency() {
  /**
   * Format number into currency
   * @param {Number} value
   * @param {String} locale
   * @param {String} currency
   */
  const formatCurrency = (value, locale = 'en-US', currency = 'USD') => {
    if (value === null || value === undefined || isNaN(value)) return '0';

    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2
    }).format(value);
  };

  /**
   * Format number into Khmer Riel (៛)
   */
  const formatKHR = value => {
    if (!value && value !== 0) return '0 ៛';

    return new Intl.NumberFormat('km-KH').format(value) + ' ៛';
  };

  return {
    formatCurrency,
    formatKHR
  };
}
