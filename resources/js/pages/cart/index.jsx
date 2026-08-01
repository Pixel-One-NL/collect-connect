import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Container from '../../components/Container';
import CartItem from '../../components/Cart/CartItem';
import EmptyCart from '../../components/Cart/EmptyCart';
import Button from '../../components/UI/Button';

export default function CartPage({ items = [], total = 0, messages = [] }) {
    const [query, setQuery] = useState('');

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) return items;
        return items.filter((item) =>
            [item.name, item.lego_number, item.color].filter(Boolean).some((v) => String(v).toLowerCase().includes(q)),
        );
    }, [items, query]);

    return (
        <Container className="max-w-4xl my-8">
            <h1 className="text-3xl font-bold mb-6">Winkelwagen</h1>

            {messages?.length > 0 && (
                <div className="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 space-y-1">
                    {messages.map((m, i) => <p key={i}>{m}</p>)}
                </div>
            )}

            {items.length === 0 ? (
                <EmptyCart />
            ) : (
                <>
                    <input
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Zoek in je winkelwagen..."
                        className="w-full border border-gray-200 rounded-lg px-4 py-2 mb-4"
                    />

                    <div className="bg-white rounded-xl border border-gray-100 divide-y">
                        {filtered.map((item) => (
                            <div key={item.id} className="px-4">
                                <CartItem item={item} />
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <p className="text-xl font-semibold">
                            Totaal: € {Number(total).toFixed(2)}
                            <span className="text-sm font-normal text-gray-500 ml-2">
                                ({items.length} artikelen)
                            </span>
                        </p>
                        <Link href="/checkout">
                            <Button variant="primary">Naar afrekenen</Button>
                        </Link>
                    </div>
                </>
            )}
        </Container>
    );
}
