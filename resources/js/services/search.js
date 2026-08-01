/**
 * @typedef {Object} SiblingColor
 * @property {number|string} id
 * @property {number} stock
 * @property {number} price
 * @property {?string} image
 * @property {{ name: string, hex: string|null }} color
 */

/**
 * @typedef {Object} ProductResult
 * @property {number|string} id
 * @property {string} title
 * @property {?string} image
 * @property {string} lego_number
 * @property {string} url
 * @property {?number} priceMin
 * @property {?number} priceMax
 * @property {SiblingColor[]} sibling_colors
 */

/**
 * @typedef {Object} MinifigResult
 * @property {number|string} id
 * @property {string} title
 * @property {?string} image
 * @property {?string} lego_number
 * @property {?string} rebrickable_id
 * @property {string} url
 * @property {?number} priceMin
 * @property {?number} priceMax
 * @property {SiblingColor[]} sibling_colors
 * @property {'minifig'} type
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
 * @property {MinifigResult[]} minifigs
 * @property {SetResult[]} sets
 */

/**
 * Searches for products, minifigs, and sets matching the given query.
 *
 * @param {string} query
 * @param {Object} [options]
 * @param {AbortSignal} [options.signal]
 * @returns {Promise<SearchResults>}
 */
export async function searchAll(query, { signal } = {}) {
    const term = query.trim();

    if (term === '') {
        return { products: [], minifigs: [], sets: [] };
    }

    const response = await fetch(`/api/search?q=${encodeURIComponent(term)}`, {
        headers: { Accept: 'application/json' },
        signal,
    });

    if (!response.ok) {
        throw new Error(`Search request failed with status ${response.status}`);
    }

    const { data } = await response.json();

    return {
        products: data?.products ?? [],
        minifigs: data?.minifigs ?? [],
        sets: data?.sets ?? [],
    };
}
