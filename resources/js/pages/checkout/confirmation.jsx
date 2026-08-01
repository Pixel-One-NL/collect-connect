import { Link } from '@inertiajs/react';
import Container from '../../components/Container';

export default function CheckoutConfirmation({ order: orderProp }) {
    const order = orderProp?.data ?? orderProp;

    return (
        <Container className="max-w-2xl my-12 space-y-4">
            <h1 className="text-3xl font-bold">Bedankt voor je bestelling</h1>
            <p className="text-gray-600">Ordernummer: <strong>{order.number}</strong></p>
            <p className="text-gray-600">Status: {order.status}</p>
            <p className="text-sm text-gray-500">
                We hebben een bevestiging voorbereid voor {order.email}. Maak een account of log in om je bestellingen later terug te zien.
            </p>
            <div className="flex gap-4">
                <Link href="/onderdelen" className="underline text-sm">Verder winkelen</Link>
                <Link href="/register" className="underline text-sm">Account aanmaken</Link>
            </div>
        </Container>
    );
}
