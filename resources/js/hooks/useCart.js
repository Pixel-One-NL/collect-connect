import { router, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';

/**
 * Provides cart state and actions for the current session cart.
 *
 * @returns {{
 *   items: Array,
 *   total: number,
 *   count: number,
 *   processing: number|null,
 *   addItem: (productId: number, quantity: number, options?: { onSuccess?: () => void }) => void,
 *   removeItem: (productId: number) => void,
 *   updateQuantity: (productId: number, quantity: number) => void,
 *   open: () => void,
 * }}
 */
export function useCart() {
    const { cart } = usePage().props;
    const [processing, setProcessing] = useState(null);

    const items = cart?.items ?? [];
    const total = cart?.total ?? 0;
    const count = items.reduce((sum, item) => sum + item.quantity, 0);

    const open = useCallback(() => {
        window.dispatchEvent(new CustomEvent('cart:open'));
    }, []);

    const addItem = useCallback((productId, quantity, { onSuccess } = {}) => {
        setProcessing(productId);
        router.post(
            '/cart/items',
            { product_id: productId, quantity },
            {
                preserveScroll: true,
                onSuccess: () => onSuccess?.(),
                onFinish: () => setProcessing(null),
            },
        );
    }, []);

    const removeItem = useCallback((productId) => {
        setProcessing(productId);
        router.delete(`/cart/items/${productId}`, {
            preserveScroll: true,
            onFinish: () => setProcessing(null),
        });
    }, []);

    const updateQuantity = useCallback((productId, quantity) => {
        setProcessing(productId);
        router.patch(
            `/cart/items/${productId}`,
            { quantity },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(null),
            },
        );
    }, []);

    return { items, total, count, processing, addItem, removeItem, updateQuantity, open };
}
