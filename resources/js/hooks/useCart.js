import { router, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { toast } from '../lib/toast';

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
    const [error, setError] = useState('');

    const items = cart?.items ?? [];
    const total = cart?.total ?? 0;
    const count = items.reduce((sum, item) => sum + item.quantity, 0);

    const open = useCallback(() => {
        window.dispatchEvent(new CustomEvent('cart:open'));
    }, []);

    const addItem = useCallback((productId, quantity, { onSuccess } = {}) => {
        setProcessing(productId);
        setError('');
        router.post(
            '/cart/items',
            { product_id: productId, quantity },
            {
                preserveScroll: true,
                onSuccess: () => onSuccess?.(),
                onError: (errors) => {
                    const message = Object.values(errors).join(' ');
                    setError(message);
                    toast({ type: 'error', title: 'Niet toegevoegd', message });
                },
                onHttpException: (response) => {
                    if (response.status === 429) {
                        toast({ type: 'error', title: 'Even geduld', message: 'Je voegt te snel producten toe. Probeer het zo opnieuw.' });
                        return false;
                    }
                },
                onFinish: () => setProcessing(null),
            },
        );
    }, []);

    const removeItem = useCallback((productId) => {
        setProcessing(productId);
        setError('');
        router.delete(`/cart/items/${productId}`, {
            preserveScroll: true,
            onError: errors => setError(Object.values(errors).join(' ')),
            onFinish: () => setProcessing(null),
        });
    }, []);

    const updateQuantity = useCallback((productId, quantity) => {
        setProcessing(productId);
        setError('');
        router.patch(
            `/cart/items/${productId}`,
            { quantity },
            {
                preserveScroll: true,
                onError: errors => setError(Object.values(errors).join(' ')),
                onFinish: () => setProcessing(null),
            },
        );
    }, []);

    return { items, total, count, processing, error, addItem, removeItem, updateQuantity, open };
}
