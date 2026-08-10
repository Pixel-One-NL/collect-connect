import { Link, router } from '@inertiajs/react';
import Container from '../../components/Container';
import InlineProduct from '../../components/Shop/Products/InlineProduct';
import AddToCartButton from '../../components/Shop/Products/AddToCartButton';

const LISTING_PATHS = {
    minifig: '/minifiguren',
    search: '/zoeken',
    part: '/onderdelen',
};

export default function CatalogPage({ title, type, query, products, filters, active }) {
    const items = products?.data ?? products ?? [];
    const meta = (products?.meta || products?.links) ? products : null;

    function updateFilter(key, value) {
        const params = { ...active, [key]: value || undefined };
        Object.keys(params).forEach((k) => {
            if (params[k] == null || params[k] === '') delete params[k];
        });
        const path = LISTING_PATHS[type] ?? LISTING_PATHS.part;
        router.get(path, params, { preserveState: true, preserveScroll: true });
    }

    return (
        <Container className="max-w-7xl my-8">
            <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900">{title}</h1>
                    {type === 'search' && (
                        <form
                            className="mt-4"
                            onSubmit={(e) => {
                                e.preventDefault();
                                updateFilter('q', e.target.q.value);
                            }}
                        >
                            <input
                                name="q"
                                defaultValue={query ?? ''}
                                placeholder="Zoek op naam of LEGO-nummer..."
                                className="w-full md:w-96 border border-gray-200 rounded-lg px-4 py-2"
                            />
                        </form>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <aside className="space-y-6">
                    {filters?.categories?.length > 0 && (
                        <div>
                            <h2 className="text-sm font-semibold text-gray-700 mb-2">Categorie</h2>
                            <select
                                className="w-full border border-gray-200 rounded-md px-3 py-2"
                                value={active?.category_id ?? ''}
                                onChange={(e) => updateFilter('category_id', e.target.value)}
                            >
                                <option value="">Alle categorieën</option>
                                {filters.categories.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                    )}
                    {filters?.colors?.length > 0 && (
                        <div>
                            <h2 className="text-sm font-semibold text-gray-700 mb-2">Kleur</h2>
                            <select
                                className="w-full border border-gray-200 rounded-md px-3 py-2"
                                value={active?.color_id ?? ''}
                                onChange={(e) => updateFilter('color_id', e.target.value)}
                            >
                                <option value="">Alle kleuren</option>
                                {filters.colors.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                    )}
                </aside>

                <div className="lg:col-span-3">
                    {items.length === 0 ? (
                        <p className="text-gray-500">Geen producten gevonden.</p>
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                            {items.map((product) => {
                                const p = product.data ?? product;
                                return (
                                    <div key={p.id} className="flex flex-col gap-2">
                                        <InlineProduct product={p} />
                                        <AddToCartButton product={p} />
                                    </div>
                                );
                            })}
                        </div>
                    )}

                    {meta?.links && (
                        <div className="mt-8 flex flex-wrap gap-2">
                            {meta.links.map((link, i) => (
                                link.url ? (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        className={`px-3 py-1 rounded border text-sm ${link.active ? 'bg-primary text-white border-primary' : 'border-gray-200'}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : null
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </Container>
    );
}
