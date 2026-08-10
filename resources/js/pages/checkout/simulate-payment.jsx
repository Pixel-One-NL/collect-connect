import { useForm } from '@inertiajs/react';
import Container from '../../components/Container';
import Price from '../../components/Shop/Price';
import Button from '../../components/UI/Button';

/**
 * Stand-in for a payment provider's hosted page. Only reachable on local and
 * staging; production redirects to the real provider instead.
 */
export default function SimulatePayment({ order }) {
    const form = useForm({ outcome: 'paid' });

    const submit = (outcome) => {
        // transform() writes to a ref, so it applies to this submit immediately.
        form.transform((data) => ({ ...data, outcome }));
        form.post(`/checkout/betaling/${order.id}`);
    };

    return (
        <Container className="max-w-lg my-12">
            <div className="rounded-xl border border-gray-200 bg-white p-6 space-y-5">
                <div className="space-y-1">
                    <p className="text-xs font-semibold uppercase tracking-wider text-gray-400">
                        Testbetaling
                    </p>
                    <h1 className="text-2xl font-bold">Betaling simuleren</h1>
                    <p className="text-sm text-gray-500">
                        Dit is geen echte betaalpagina. Er wordt geen geld verwerkt.
                    </p>
                </div>

                <dl className="divide-y divide-gray-100 border-y border-gray-100 text-sm">
                    <div className="flex justify-between py-2">
                        <dt className="text-gray-500">Bestelling</dt>
                        <dd className="font-medium">{order.number}</dd>
                    </div>
                    <div className="flex justify-between py-2">
                        <dt className="text-gray-500">Betaalmethode</dt>
                        <dd className="font-medium">{order.payment_method}</dd>
                    </div>
                    <div className="flex justify-between py-2">
                        <dt className="text-gray-500">Te betalen</dt>
                        <dd className="font-semibold">
                            <Price price={order.total_cents} />
                        </dd>
                    </div>
                </dl>

                <div className="flex flex-col gap-2">
                    <Button
                        type="button"
                        variant="primary"
                        className="w-full justify-center"
                        disabled={form.processing}
                        onClick={() => submit('paid')}
                    >
                        Betaling slagen
                    </Button>
                    <Button
                        type="button"
                        className="w-full justify-center border border-gray-200"
                        disabled={form.processing}
                        onClick={() => submit('failed')}
                    >
                        Betaling laten mislukken
                    </Button>
                </div>
            </div>
        </Container>
    );
}
