import { MinusIcon } from '@phosphor-icons/react/dist/csr/Minus';
import { PlusIcon } from '@phosphor-icons/react/dist/csr/Plus';
import { ShoppingCartIcon } from '@phosphor-icons/react/dist/csr/ShoppingCart';
import Button from '../../UI/Button';
import { useCart } from '../../../hooks/useCart';

/**
 * Universal add-to-cart button. Persists cart state — once an item is in the
 * cart the button becomes a quantity stepper reflecting the current cart count.
 *
 * Uses a @container wrapper so internal layout responds to available width
 * rather than the viewport.
 *
 * @param {Object} props
 * @param {{ id: number, stock: number }} props.product
 */
export default function AddToCartButton({ product }) {
    const { items, addItem, updateQuantity, removeItem, processing } = useCart();

    const currentCartQty = items.find((i) => i.id === product.id)?.quantity ?? 0;
    const available = product.stock - currentCartQty;
    const isProcessing = processing === product.id;

    if (product.stock === 0 && currentCartQty === 0) {
        return (
            <p className="py-1 text-center text-sm text-gray-500">Niet op voorraad</p>
        );
    }

    if (currentCartQty > 0) {
        return (
            <div className="flex w-full items-center overflow-hidden rounded-md border border-gray-200">
                <button
                    className="flex flex-1 cursor-pointer items-center justify-center px-3 py-2 transition hover:bg-gray-100 disabled:opacity-40"
                    onClick={() =>
                        currentCartQty === 1
                            ? removeItem(product.id)
                            : updateQuantity(product.id, currentCartQty - 1)
                    }
                    disabled={isProcessing}
                >
                    <MinusIcon size={16} />
                </button>

                <span className="min-w-10 px-2 py-2 text-center text-sm font-medium tabular-nums">
                    {currentCartQty}
                </span>

                <button
                    className="flex flex-1 cursor-pointer items-center justify-center px-3 py-2 transition hover:bg-gray-100 disabled:opacity-40"
                    onClick={() => updateQuantity(product.id, currentCartQty + 1)}
                    disabled={isProcessing || available === 0}
                >
                    <PlusIcon size={16} />
                </button>
            </div>
        );
    }

    return (
        <div className="@container w-full">
            <Button
                variant="primary"
                onClick={() => addItem(product.id, 1)}
                disabled={isProcessing}
                className="w-full justify-center"
            >
                <ShoppingCartIcon size={18} />
                <span className="hidden @[160px]:inline">
                    {isProcessing ? 'Toevoegen...' : 'In winkelmandje'}
                </span>
            </Button>
        </div>
    );
}
