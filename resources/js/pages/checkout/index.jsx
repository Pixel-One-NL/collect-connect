import { router, useForm } from '@inertiajs/react';
import Container from '../../components/Container';
import Button from '../../components/UI/Button';

export default function CheckoutPage({
    items = [],
    subtotal = 0,
    weight_grams = 0,
    shippingMethods = [],
    paymentMethods = [],
    defaultAddress = null,
    user = null,
    country = 'NL',
}) {
    const methods = shippingMethods.map((m) => ({
        id: m.id,
        name: m.name,
        price_cents: m.price_cents ?? m.priceCents ?? 0,
        code: m.code,
    }));

    const first = methods[0];

    const form = useForm({
        name: defaultAddress?.name ?? user?.name ?? '',
        email: defaultAddress?.email ?? user?.email ?? '',
        phone: defaultAddress?.phone ?? '',
        line1: defaultAddress?.line1 ?? '',
        line2: defaultAddress?.line2 ?? '',
        postal_code: defaultAddress?.postal_code ?? '',
        city: defaultAddress?.city ?? '',
        country_code: defaultAddress?.country_code ?? country ?? 'NL',
        shipping_method_id: first?.id ?? 0,
        shipping_method_name: first?.name ?? '',
        shipping_cents: first?.price_cents ?? 0,
        payment_method: paymentMethods[0]?.id ?? 'ideal',
        create_account: !user,
        password: '',
    });

    function selectShipping(method) {
        form.setData({
            ...form.data,
            shipping_method_id: method.id,
            shipping_method_name: method.name,
            shipping_cents: method.price_cents,
        });
    }

    function submit(e) {
        e.preventDefault();
        form.post('/checkout');
    }

    const shippingEuro = (form.data.shipping_cents || 0) / 100;
    const total = Number(subtotal) + shippingEuro;

    return (
        <Container className="max-w-5xl my-8">
            <h1 className="text-3xl font-bold mb-6">Afrekenen</h1>

            <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-6">
                    <section className="border border-gray-200 rounded-xl p-6 space-y-3">
                        <h2 className="font-semibold text-lg">Gegevens</h2>
                        <input className="w-full border rounded-md px-3 py-2" placeholder="Naam" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                        <input className="w-full border rounded-md px-3 py-2" type="email" placeholder="E-mail" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                        <input className="w-full border rounded-md px-3 py-2" placeholder="Telefoon" value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} />
                        <input className="w-full border rounded-md px-3 py-2" placeholder="Adres" value={form.data.line1} onChange={(e) => form.setData('line1', e.target.value)} required />
                        <input className="w-full border rounded-md px-3 py-2" placeholder="Toevoeging" value={form.data.line2} onChange={(e) => form.setData('line2', e.target.value)} />
                        <div className="grid grid-cols-2 gap-3">
                            <input className="border rounded-md px-3 py-2" placeholder="Postcode" value={form.data.postal_code} onChange={(e) => form.setData('postal_code', e.target.value)} required />
                            <input className="border rounded-md px-3 py-2" placeholder="Plaats" value={form.data.city} onChange={(e) => form.setData('city', e.target.value)} required />
                        </div>
                        <select
                            className="w-full border rounded-md px-3 py-2"
                            value={form.data.country_code}
                            onChange={(e) => {
                                form.setData('country_code', e.target.value);
                                router.get('/checkout', { country: e.target.value }, { preserveState: true, preserveScroll: true });
                            }}
                        >
                            <option value="NL">Nederland</option>
                            <option value="BE">België</option>
                            <option value="DE">Duitsland</option>
                        </select>

                        {!user && (
                            <div className="pt-2 border-t">
                                <label className="flex items-center gap-2 text-sm">
                                    <input type="checkbox" checked={form.data.create_account} onChange={(e) => form.setData('create_account', e.target.checked)} />
                                    Account aanmaken
                                </label>
                                {form.data.create_account && (
                                    <input className="w-full border rounded-md px-3 py-2 mt-2" type="password" placeholder="Wachtwoord" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} />
                                )}
                            </div>
                        )}
                    </section>

                    <section className="border border-gray-200 rounded-xl p-6 space-y-3">
                        <h2 className="font-semibold text-lg">Verzending</h2>
                        {methods.map((method) => (
                            <label key={method.id} className="flex items-center justify-between gap-4 border rounded-lg p-3 cursor-pointer">
                                <span className="flex items-center gap-2">
                                    <input
                                        type="radio"
                                        name="shipping"
                                        checked={form.data.shipping_method_id === method.id}
                                        onChange={() => selectShipping(method)}
                                    />
                                    {method.name}
                                </span>
                                <span>€ {(method.price_cents / 100).toFixed(2)}</span>
                            </label>
                        ))}
                    </section>

                    <section className="border border-gray-200 rounded-xl p-6 space-y-3">
                        <h2 className="font-semibold text-lg">Betaling</h2>
                        {paymentMethods.map((method) => (
                            <label key={method.id} className="flex items-center gap-2 border rounded-lg p-3 cursor-pointer">
                                <input
                                    type="radio"
                                    name="payment"
                                    checked={form.data.payment_method === method.id}
                                    onChange={() => form.setData('payment_method', method.id)}
                                />
                                {method.label}
                            </label>
                        ))}
                    </section>
                </div>

                <aside className="border border-gray-200 rounded-xl p-6 h-fit space-y-3">
                    <h2 className="font-semibold text-lg">Overzicht</h2>
                    <ul className="text-sm space-y-1 max-h-60 overflow-y-auto">
                        {items.map((item) => (
                            <li key={item.id} className="flex justify-between gap-2">
                                <span className="truncate">{item.name} ×{item.quantity}</span>
                                <span>€ {(item.price * item.quantity).toFixed(2)}</span>
                            </li>
                        ))}
                    </ul>
                    <div className="border-t pt-3 text-sm space-y-1">
                        <div className="flex justify-between"><span>Subtotaal</span><span>€ {Number(subtotal).toFixed(2)}</span></div>
                        <div className="flex justify-between"><span>Verzendgewicht</span><span>{Number(weight_grams).toFixed(1)} g</span></div>
                        <div className="flex justify-between"><span>Verzending</span><span>€ {shippingEuro.toFixed(2)}</span></div>
                        <div className="flex justify-between font-semibold text-base"><span>Totaal</span><span>€ {total.toFixed(2)}</span></div>
                    </div>
                    <Button type="submit" variant="primary" className="w-full justify-center" disabled={form.processing}>
                        {form.processing ? 'Bezig...' : 'Bestelling plaatsen'}
                    </Button>
                    {Object.keys(form.errors).length > 0 && (
                        <p className="text-sm text-red-600">{Object.values(form.errors)[0]}</p>
                    )}
                </aside>
            </form>
        </Container>
    );
}
