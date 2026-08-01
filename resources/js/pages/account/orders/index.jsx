import { Link } from '@inertiajs/react';
import Container from '../../../components/Container';

export default function OrdersIndex({ orders }) {
    const rows = orders?.data ?? [];

    return (
        <Container className="max-w-4xl my-8">
            <h1 className="text-3xl font-bold mb-6">Mijn bestellingen</h1>
            {rows.length === 0 ? (
                <p className="text-gray-500">Je hebt nog geen bestellingen.</p>
            ) : (
                <div className="space-y-3">
                    {rows.map((order) => (
                        <Link key={order.id} href={`/account/orders/${order.id}`} className="block border rounded-xl p-4 hover:border-gray-400">
                            <div className="flex justify-between gap-4">
                                <div>
                                    <p className="font-semibold">{order.number}</p>
                                    <p className="text-sm text-gray-500">{order.status}</p>
                                </div>
                                <p className="font-medium">€ {(order.total_cents / 100).toFixed(2)}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </Container>
    );
}
