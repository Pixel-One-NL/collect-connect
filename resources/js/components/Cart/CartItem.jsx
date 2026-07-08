import { MinusIcon } from '@phosphor-icons/react/dist/csr/Minus';
import { PlusIcon } from '@phosphor-icons/react/dist/csr/Plus';
import { TrashIcon } from '@phosphor-icons/react/dist/csr/Trash';
import { ShoppingBagIcon } from '@phosphor-icons/react/dist/csr/ShoppingBag';
import { useCart } from '../../hooks/useCart';

/**
 * @param {Object} props
 * @param {{ id: number, name: string, image: string|null, price: number, quantity: number, stock: number, color: string|null, color_hex: string|null }} props.item
 */
export default function CartItem({ item }) {
    const { removeItem, updateQuantity, processing } = useCart();
    const isProcessing = processing === item.id;

    return (
        <div className={`flex gap-3 py-4 border-b border-gray-100 last:border-b-0 transition-opacity ${isProcessing ? 'opacity-50 pointer-events-none' : ''}`}>
            <div className="w-16 h-16 rounded-lg overflow-hidden bg-gray-50 flex-shrink-0 flex items-center justify-center">
                {item.image ? (
                    <img
                        src={item.image}
                        alt={item.name}
                        className="object-contain w-full h-full"
                    />
                ) : (
                    <ShoppingBagIcon size={24} className="text-gray-300" />
                )}
            </div>

            <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">{item.name}</p>

                {item.color && (
                    <div className="flex items-center gap-1.5 mt-0.5">
                        {item.color_hex && (
                            <span
                                className="w-3 h-3 rounded-full border border-gray-200 inline-block flex-shrink-0"
                                style={{ backgroundColor: `#${item.color_hex}` }}
                            />
                        )}
                        <span className="text-xs text-gray-400">{item.color}</span>
                    </div>
                )}

                <p className="text-sm text-gray-500 mt-1">&euro; {item.price.toFixed(2)}</p>

                <div className="flex items-center justify-between mt-2">
                    <div className="inline-flex items-center border border-gray-200 rounded-md overflow-hidden">
                        <button
                            className="px-2 py-1 hover:bg-gray-100 transition disabled:opacity-40 cursor-pointer"
                            onClick={() => updateQuantity(item.id, item.quantity - 1)}
                            disabled={item.quantity <= 1}
                            aria-label="Decrease quantity"
                        >
                            <MinusIcon size={12} />
                        </button>

                        <span className="px-2 py-1 min-w-8 text-center text-sm tabular-nums">
                            {item.quantity}
                        </span>

                        <button
                            className="px-2 py-1 hover:bg-gray-100 transition disabled:opacity-40 cursor-pointer"
                            onClick={() => updateQuantity(item.id, item.quantity + 1)}
                            disabled={item.quantity >= item.stock}
                            aria-label="Increase quantity"
                        >
                            <PlusIcon size={12} />
                        </button>
                    </div>

                    <button
                        className="p-1.5 text-gray-400 hover:text-red-500 transition cursor-pointer"
                        onClick={() => removeItem(item.id)}
                        aria-label="Remove item"
                    >
                        <TrashIcon size={16} />
                    </button>
                </div>
            </div>
        </div>
    );
}
