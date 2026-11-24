import { ShoppingCartIcon } from '@phosphor-icons/react/dist/csr/ShoppingCart';
import { Link } from '@inertiajs/react';
import { useState } from 'react';
import Price from '../Price.jsx';
import StockIndicator from './StockIndicator.jsx';
import { useCart } from '../../../hooks/useCart';

/**
 * Image of a color variant, or a swatch of the same size when there is no
 * image (or it fails to load).
 *
 * @param {Object} props
 * @param {Object} props.variant
 */
function VariantImage({ variant }) {
    const [failed, setFailed] = useState(false);
    const name = variant.color?.name ?? '';

    if (!variant.image || failed) {
        return (
            <span
                className="block h-9 w-9 shrink-0 rounded border border-gray-200"
                style={{ backgroundColor: `#${variant.color?.hex ?? 'e5e7eb'}` }}
                role="img"
                aria-label={name}
            />
        );
    }

    return (
        <img
            src={variant.image}
            alt={name}
            loading="lazy"
            onError={() => setFailed(true)}
            className="h-9 w-9 shrink-0 rounded object-contain"
        />
    );
}

/**
 * Quantity input plus icon-only add-to-cart button for one variant.
 *
 * @param {Object} props
 * @param {Object} props.variant
 */
function VariantAddToCart({ variant }) {
    const { items, addItem, processing } = useCart();
    const [quantity, setQuantity] = useState(1);

    const inCart = items.find((item) => item.id === variant.id)?.quantity ?? 0;
    const available = Math.max(0, variant.stock - inCart);
    const isProcessing = processing === variant.id;
    const name = variant.color?.name ?? '';

    if (available === 0) {
        return (
            <span className="block text-right text-xs text-gray-400">
                {variant.stock > 0 ? `${inCart} in winkelwagen` : 'Uitverkocht'}
            </span>
        );
    }

    return (
        <form
            className="flex items-center justify-end gap-1.5"
            onSubmit={(event) => {
                event.preventDefault();
                addItem(variant.id, Math.min(quantity, available), { onSuccess: () => setQuantity(1) });
            }}
        >
            <input
                type="number"
                inputMode="numeric"
                min={1}
                max={available}
                value={quantity}
                onChange={(event) => setQuantity(Math.max(1, Math.min(available, parseInt(event.target.value, 10) || 1)))}
                aria-label={`Aantal ${name}`}
                className="h-9 w-14 rounded-md border border-gray-200 px-2 text-center text-sm tabular-nums [appearance:textfield] focus:border-primary focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
            />
            <button
                type="submit"
                disabled={isProcessing}
                aria-label={`${name} in winkelmandje`}
                title="In winkelmandje"
                className="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-md bg-primary text-white transition hover:bg-primary/90 disabled:cursor-wait disabled:opacity-50"
            >
                <ShoppingCartIcon size={18} />
            </button>
        </form>
    );
}

/**
 * Every color variant of a part with its stock and price.
 *
 * @param {Object} props
 * @param {Array<Object>} props.variants - `sibling_colors` from ProductResource.
 * @param {number} props.currentId - The product currently shown.
 */
export default function ColorVariantsTable({ variants, currentId }) {
    const sorted = [...variants].sort((a, b) => {
        if (a.id === currentId) return -1;
        if (b.id === currentId) return 1;
        if ((a.stock > 0) !== (b.stock > 0)) return a.stock > 0 ? -1 : 1;
        return (a.color?.name ?? '').localeCompare(b.color?.name ?? '', 'nl');
    });

    return (
        <section className="mt-8" aria-labelledby="color-variants-heading">
            <div className="mb-3 flex items-baseline justify-between gap-4">
                <h2 id="color-variants-heading" className="text-xl font-semibold">Beschikbare kleuren</h2>
                <span className="text-sm text-gray-500">{variants.length} kleuren</span>
            </div>

            <div className="overflow-hidden rounded-lg border border-gray-200">
                <table className="w-full text-left text-sm">
                    <thead className="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th scope="col" className="px-3 py-2 font-medium">Kleur</th>
                            <th scope="col" className="hidden px-3 py-2 font-medium sm:table-cell">Voorraad</th>
                            <th scope="col" className="px-3 py-2 text-right font-medium">Prijs</th>
                            <th scope="col" className="px-3 py-2"><span className="sr-only">In winkelmandje</span></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {sorted.map((variant) => {
                            const isCurrent = variant.id === currentId;

                            return (
                                <tr
                                    key={variant.id}
                                    className={isCurrent ? 'bg-accent/60' : 'hover:bg-gray-50'}
                                    aria-current={isCurrent ? 'true' : undefined}
                                >
                                    <td className="px-3 py-1.5">
                                        <Link href={variant.url} className="flex min-w-0 items-center gap-2.5">
                                            <VariantImage variant={variant} />
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium text-gray-900 hover:underline">
                                                    {variant.color?.name}
                                                    {isCurrent && <span className="sr-only"> (huidige kleur)</span>}
                                                </span>
                                                <span className="sm:hidden"><StockIndicator stock={variant.stock} /></span>
                                            </span>
                                        </Link>
                                    </td>
                                    <td className="hidden whitespace-nowrap px-3 py-1.5 sm:table-cell">
                                        <StockIndicator stock={variant.stock} />
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-1.5 text-right font-semibold text-gray-900">
                                        <Price price={variant.price} />
                                    </td>
                                    <td className="w-px px-3 py-1.5">
                                        <VariantAddToCart variant={variant} />
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </section>
    );
}
