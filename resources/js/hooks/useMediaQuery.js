import { useEffect, useState } from 'react';

/**
 * Tracks a CSS media query from JavaScript, so a component can pick between
 * two layouts by *rendering* one of them instead of hiding one with CSS.
 *
 * @param {string} query - e.g. '(min-width: 768px)'
 * @returns {boolean}
 */
export function useMediaQuery(query) {
    const [matches, setMatches] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.matchMedia(query).matches;
    });

    useEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        const mediaQuery = window.matchMedia(query);
        const handleChange = (event) => setMatches(event.matches);

        setMatches(mediaQuery.matches);
        mediaQuery.addEventListener('change', handleChange);

        return () => mediaQuery.removeEventListener('change', handleChange);
    }, [query]);

    return matches;
}
