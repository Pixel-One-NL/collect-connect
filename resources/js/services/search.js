/**
 * @typedef {Object} ProductColor
 * @property {string} name
 * @property {string} hex
 * @property {number} stock
 */

/**
 * @typedef {Object} ProductResult
 * @property {number|string} id
 * @property {string} name
 * @property {?string} image
 * @property {string} lego_number
 * @property {string} url
 * @property {?number} priceMin
 * @property {?number} priceMax
 * @property {?ProductColor} primaryColor
 * @property {ProductColor[]} colors
 */

/**
 * @typedef {Object} SetResult
 * @property {number|string} id
 * @property {string} set_num
 * @property {string} name
 * @property {number} year
 * @property {number} num_parts
 * @property {?string} image
 * @property {string} url
 */

/**
 * @typedef {Object} SearchResults
 * @property {ProductResult[]} products
 * @property {SetResult[]} sets
 */

/**
 * Searches for products and sets matching the given query.
 *
 * @param {string} query
 * @param {Object} [options]
 * @param {AbortSignal} [options.signal]
 * @returns {Promise<SearchResults>}
 */
export async function searchAll(query, { signal } = {}) {
    const term = query.trim();

    if (term === '') {
        return { products: [], sets: [] };
    }

    const response = await fetch(`/api/search?q=${encodeURIComponent(term)}`, {
        headers: { Accept: 'application/json' },
        signal,
    });

    if (!response.ok) {
        throw new Error(`Search request failed with status ${response.status}`);
    }

    const { data } = await response.json();

    return data;
}
