import { MagnifyingGlassIcon } from "@phosphor-icons/react/dist/csr/MagnifyingGlass";
import { SpinnerGapIcon } from "@phosphor-icons/react/dist/csr/SpinnerGap";
import { useEffect, useState } from "react";
import { searchAll } from "../../services/search.js";
import InlineProduct from "../Shop/Products/InlineProduct.jsx";
import InlineSet from "../Shop/Sets/InlineSet.jsx";

/**
 * @param {Object} props
 * @param {string} props.query
 */
export default function SearchResults({ query }) {
    const [results, setResults] = useState({ products: [], sets: [] });
    const [isLoading, setIsLoading] = useState(false);
    const [activeTab, setActiveTab] = useState('products');

    const trimmedQuery = query.trim();

    useEffect(() => {
        if (trimmedQuery === "") {
            setResults({ products: [], sets: [] });
            setIsLoading(false);
            return;
        }

        const controller = new AbortController();
        setIsLoading(true);

        const timer = setTimeout(async () => {
            try {
                const data = await searchAll(trimmedQuery, { signal: controller.signal });
                setResults(data);
                setIsLoading(false);
                // Auto-select the tab that has results
                if (data.products.length === 0 && data.sets.length > 0) {
                    setActiveTab('sets');
                } else {
                    setActiveTab('products');
                }
            } catch (error) {
                if (error.name !== "AbortError") {
                    setResults({ products: [], sets: [] });
                    setIsLoading(false);
                }
            }
        }, 250);

        return () => {
            clearTimeout(timer);
            controller.abort();
        };
    }, [trimmedQuery]);

    if (trimmedQuery === "") {
        return (
            <div className="h-full flex items-center justify-center px-4">
                <div className="space-y-4 text-center">
                    <div className="bg-gray-200 text-gray-400 aspect-square rounded-full w-24 h-24 inline-flex items-center justify-center">
                        <MagnifyingGlassIcon size={48} />
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">
                        Typ je zoekopdracht om te starten met zoeken
                    </h3>
                </div>
            </div>
        );
    }

    const hasProducts = results.products.length > 0;
    const hasSets = results.sets.length > 0;
    const hasAny = hasProducts || hasSets;
    const hasBoth = hasProducts && hasSets;

    const loadingOrEmpty = isLoading ? (
        <div className="flex items-center justify-center py-12 text-gray-400">
            <SpinnerGapIcon size={32} className="animate-spin" />
        </div>
    ) : !hasAny ? (
        <p className="py-12 text-center text-gray-500">
            Geen resultaten gevonden voor <span className="font-semibold text-gray-900">"{query}"</span>.
        </p>
    ) : null;

    return (
        <div className="flex h-full flex-col">
            <div className="border-b border-gray-200 px-4 lg:px-8 py-4 shrink-0">
                <p className="text-gray-500">
                    Zoekresultaten voor <span className="font-semibold text-gray-900">"{query}"</span>…
                </p>
            </div>

            {/* ── Mobile layout ─────────────────────────────────────────── */}
            <div className="flex flex-1 min-h-0 flex-col lg:hidden">
                {loadingOrEmpty ? (
                    <div className="flex-1">{loadingOrEmpty}</div>
                ) : (
                    <>
                        {/* Floating tab switcher — sits above the scroll area */}
                        {hasBoth && (
                            <div className="shrink-0 flex gap-2 px-4 py-3 bg-white shadow-md z-10">
                                <button
                                    onClick={() => setActiveTab('products')}
                                    className={`flex-1 rounded-md px-4 py-2 text-sm font-medium transition border ${
                                        activeTab === 'products'
                                            ? 'bg-primary text-white border-primary'
                                            : 'bg-white text-gray-600 border-gray-200 hover:bg-accent'
                                    }`}
                                >
                                    Onderdelen ({results.products.length})
                                </button>
                                <button
                                    onClick={() => setActiveTab('sets')}
                                    className={`flex-1 rounded-md px-4 py-2 text-sm font-medium transition border ${
                                        activeTab === 'sets'
                                            ? 'bg-primary text-white border-primary'
                                            : 'bg-white text-gray-600 border-gray-200 hover:bg-accent'
                                    }`}
                                >
                                    Sets ({results.sets.length})
                                </button>
                            </div>
                        )}

                        {/* Sliding track — each panel scrolls independently */}
                        <div className="flex-1 min-h-0 overflow-hidden">
                            <div
                                className="flex h-full transition-transform duration-300 ease-in-out"
                                style={{
                                    width: '200%',
                                    transform: `translateX(${activeTab === 'sets' ? '-50%' : '0%'})`,
                                }}
                            >
                                <div className="overflow-y-auto h-full px-4 py-4" style={{ width: '50%' }}>
                                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                        {results.products.map((product) => (
                                            <InlineProduct key={product.id} product={product} />
                                        ))}
                                    </div>
                                </div>
                                <div className="overflow-y-auto h-full px-4 py-4" style={{ width: '50%' }}>
                                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                        {results.sets.map((set) => (
                                            <InlineSet key={set.id} set={set} />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </>
                )}
            </div>

            {/* ── Desktop layout ────────────────────────────────────────── */}
            <div className="hidden lg:flex lg:flex-1 lg:min-h-0 lg:flex-col lg:overflow-y-auto lg:px-8 lg:py-6">
                {loadingOrEmpty ?? (
                    <div className="flex flex-col gap-10">
                        {hasProducts && (
                            <section>
                                <h4 className="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-4">
                                    Onderdelen
                                </h4>
                                <div className="grid grid-cols-4 gap-4 xl:grid-cols-5">
                                    {results.products.map((product) => (
                                        <InlineProduct key={product.id} product={product} />
                                    ))}
                                </div>
                            </section>
                        )}
                        {hasSets && (
                            <section>
                                <h4 className="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-4">
                                    Sets
                                </h4>
                                <div className="grid grid-cols-4 gap-4 xl:grid-cols-5">
                                    {results.sets.map((set) => (
                                        <InlineSet key={set.id} set={set} />
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
