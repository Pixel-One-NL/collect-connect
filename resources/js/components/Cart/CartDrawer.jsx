import { ShoppingCartIcon } from '@phosphor-icons/react/dist/csr/ShoppingCart';
import { XIcon } from '@phosphor-icons/react/dist/csr/X';
import { useEffect } from 'react';
import EmptyCart from './EmptyCart.jsx';
import CartItem from './CartItem.jsx';
import Button from '../UI/Button.jsx';
import { useCart } from '../../hooks/useCart.js';

/**
 * @param {Object} props
 * @param {boolean} props.isOpen
 * @param {() => void} props.onClose
 */
export default function CartDrawer({ isOpen, onClose }) {
    const { items, total } = useCart();

    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key !== 'Escape') {
                return;
            }
            onClose();
        };

        window.addEventListener('keydown', handleEscape);
        return () => window.removeEventListener('keydown', handleEscape);
    }, [onClose]);

    return (
        <>
            {isOpen && (
                <div
                    className="fixed inset-0 bg-black/30 z-50 transition-opacity duration-300"
                    onClick={onClose}
                    aria-hidden="true"
                />
            )}

            <div className={`fixed right-0 top-0 h-full w-full max-w-md bg-white z-50 transform transition-transform duration-300 ease-in-out md:rounded-l-xl flex flex-col ${isOpen ? 'translate-x-0' : 'translate-x-full'}`}>
                <div className="sticky top-0 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <div className="inline-flex gap-2 items-center">
                        <ShoppingCartIcon size={24} />
                        <h2 className="text-lg font-semibold text-gray-900">Winkelwagen</h2>
                    </div>

                    <Button onClick={onClose} iconOnly>
                        <XIcon weight="bold" />
                    </Button>
                </div>

                <div className="h-full px-6 py-4 overflow-y-auto">
                    {items.length === 0 ? (
                        <div className="flex items-center justify-center h-full">
                            <EmptyCart />
                        </div>
                    ) : (
                        items.map((item) => (
                            <CartItem key={item.id} item={item} />
                        ))
                    )}
                </div>

                <div className="px-6 py-4 border-t border-gray-200 bg-white rounded-bl-xl space-y-3">
                    <div className="flex items-center justify-between text-sm">
                        <span className="text-gray-500">Total</span>
                        <span className="text-lg font-semibold">
                            &euro; {total.toFixed(2)}
                        </span>
                    </div>

                    <button
                        className="w-full bg-primary text-white py-3 rounded-lg font-medium opacity-50 cursor-not-allowed"
                        disabled
                    >
                        Checkout
                    </button>
                </div>
            </div>
        </>
    );
}
