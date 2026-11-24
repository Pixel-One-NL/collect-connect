import { Link } from '@inertiajs/react';
import { useState } from 'react';
import Price from '../Price.jsx';
import StockIndicator from './StockIndicator.jsx';
import AddToCartButton from './AddToCartButton.jsx';

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
                className="block h-14 w-14 shrink-0 rounded-md border border-gray-200"
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
            className="h-14 w-14 shrink-0 rounded-md object-contain"
        />
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
        <section className="mt-10" aria-labelledby="color-variants-heading">
            <div className="mb-4 flex items-baseline justify-between gap-4">
                <h2 id="color-variants-heading" className="text-2xl font-semibold">Beschikbare kleuren</h2>
                <span className="text-sm text-gray-500">{variants.length} kleuren</span>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-200">
                <table className="w-full text-left text-sm">
                    <thead className="hidden bg-gray-50 text-xs uppercase tracking-wide text-gray-500 sm:table-header-group">
                        <tr>
                            <th scope="col" className="px-4 py-3 font-medium">Kleur</th>
                            <th scope="col" className="px-4 py-3 font-medium">Voorraad</th>
                            <th scope="col" className="px-4 py-3 font-medium">Prijs</th>
                            <th scope="col" className="w-48 px-4 py-3 font-medium"><span className="sr-only">Actie</span></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {sorted.map((variant) => {
                            const isCurrent = variant.id === currentId;

                            return (
                                <tr
                                    key={variant.id}
                                    className={`grid grid-cols-[auto_1fr] items-center gap-x-3 gap-y-2 p-3 sm:table-row sm:p-0 ${isCurrent ? 'bg-accent/60' : 'hover:bg-gray-50'}`}
                                    aria-current={isCurrent ? 'true' : undefined}
                                >
                                    <td className="row-span-3 sm:px-4 sm:py-3">
                                        <Link href={variant.url} className="flex items-center gap-3">
                                            <VariantImage variant={variant} />
                                            <span className="hidden font-medium text-gray-900 sm:inline">
                                                {variant.color?.name}
                                                {isCurrent && <span className="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-white">Huidig</span>}
                                            </span>
                                        </Link>
                                    </td>
                                    <td className="sm:hidden">
                                        <Link href={variant.url} className="font-medium text-gray-900">
                                            {variant.color?.name}
                                        </Link>
                                        {isCurrent && <span className="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-white">Huidig</span>}
                                    </td>
                                    <td className="flex items-center gap-3 sm:table-cell sm:px-4 sm:py-3">
                                        <StockIndicator stock={variant.stock} />
                                        <span className="font-semibold text-gray-900 sm:hidden"><Price price={variant.price} /></span>
                                    </td>
                                    <td className="hidden font-semibold text-gray-900 sm:table-cell sm:px-4 sm:py-3">
                                        <Price price={variant.price} />
                                    </td>
                                    <td className="col-start-2 sm:px-4 sm:py-3">
                                        <AddToCartButton product={variant} />
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
