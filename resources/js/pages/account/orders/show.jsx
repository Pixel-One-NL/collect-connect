import { Link } from '@inertiajs/react';
import Container from '../../../components/Container';

export default function OrderShow({ order: orderProp }) {
    const order = orderProp?.data ?? orderProp;
    const items = order.items?.data ?? order.items ?? [];

    return (
        <Container className="max-w-3xl my-8 space-y-6">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-bold">{order.number}</h1>
                    <p className="text-gray-500">Status: {order.status}{order.bricqer_status ? ` / ERP: ${order.bricqer_status}` : ''}</p>
                </div>
                <a href={`/account/orders/${order.id}/invoice`} className="text-sm underline">Factuur downloaden</a>
            </div>

            <section className="border rounded-xl p-4 space-y-2">
                <h2 className="font-semibold">Verzending</h2>
                <p className="text-sm">{order.name}<br />{order.shipping_line1}<br />{order.shipping_postal_code} {order.shipping_city}<br />{order.shipping_country_code}</p>
                <p className="text-sm text-gray-600">{order.shipping_method_name}</p>
                {order.tracking_code && (
                    <p className="text-sm">
                        Track &amp; trace: {order.tracking_url ? <a className="underline" href={order.tracking_url}>{order.tracking_code}</a> : order.tracking_code}
                    </p>
                )}
            </section>

            <section className="border rounded-xl p-4">
                <h2 className="font-semibold mb-3">Artikelen</h2>
                <ul className="space-y-2 text-sm">
                    {items.map((item) => (
                        <li key={item.id} className="flex justify-between gap-3">
                            <span>{item.title} {item.color_name ? `(${item.color_name})` : ''} ×{item.quantity}</span>
                            <span>€ {((item.unit_price_cents * item.quantity) / 100).toFixed(2)}</span>
                        </li>
                    ))}
                </ul>
                <div className="border-t mt-4 pt-3 text-sm space-y-1">
                    <div className="flex justify-between"><span>Subtotaal</span><span>€ {(order.subtotal_cents / 100).toFixed(2)}</span></div>
                    <div className="flex justify-between"><span>Verzending</span><span>€ {(order.shipping_cents / 100).toFixed(2)}</span></div>
                    <div className="flex justify-between font-semibold"><span>Totaal</span><span>€ {(order.total_cents / 100).toFixed(2)}</span></div>
                </div>
            </section>

            <Link href="/account/orders" className="text-sm underline">Terug naar bestellingen</Link>
        </Container>
    );
}
