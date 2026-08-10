import { router } from '@inertiajs/react';
import Input from '../UI/Input';
import Select from '../UI/Select';
import CheckoutSection from './CheckoutSection';

const COUNTRIES = [
    { code: 'NL', label: 'Nederland' },
    { code: 'BE', label: 'België' },
    { code: 'DE', label: 'Duitsland' },
];

/**
 * @param {Object} props
 * @param {import('@inertiajs/react').InertiaFormProps} props.form
 */
export default function ShippingAddressSection({ form }) {
    // Shipping methods and payment methods are country dependent, so the server
    // re-resolves them when the country changes.
    const changeCountry = (code) => {
        form.setData('country_code', code);
        router.get('/checkout', { country: code }, { preserveState: true, preserveScroll: true });
    };

    return (
        <CheckoutSection title="Verzendinformatie">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input
                    label="Voornaam"
                    autoComplete="given-name"
                    required
                    value={form.data.first_name}
                    error={form.errors.name}
                    onChange={(e) => form.setData('first_name', e.target.value)}
                />
                <Input
                    label="Achternaam"
                    autoComplete="family-name"
                    required
                    value={form.data.last_name}
                    onChange={(e) => form.setData('last_name', e.target.value)}
                />
            </div>

            <Input
                label="Bedrijf"
                optional
                autoComplete="organization"
                value={form.data.company}
                error={form.errors.company}
                onChange={(e) => form.setData('company', e.target.value)}
            />

            <Input
                label="Adres"
                autoComplete="street-address"
                required
                value={form.data.line1}
                error={form.errors.line1}
                onChange={(e) => form.setData('line1', e.target.value)}
            />

            <Input
                label="Appartement, suite, enz."
                optional
                value={form.data.line2}
                error={form.errors.line2}
                onChange={(e) => form.setData('line2', e.target.value)}
            />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Input
                    label="Postcode"
                    autoComplete="postal-code"
                    required
                    value={form.data.postal_code}
                    error={form.errors.postal_code}
                    onChange={(e) => form.setData('postal_code', e.target.value)}
                />
                <Input
                    label="Stad"
                    autoComplete="address-level2"
                    required
                    value={form.data.city}
                    error={form.errors.city}
                    onChange={(e) => form.setData('city', e.target.value)}
                />
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Select
                    label="Land"
                    value={form.data.country_code}
                    error={form.errors.country_code}
                    onChange={(e) => changeCountry(e.target.value)}
                >
                    {COUNTRIES.map((country) => (
                        <option key={country.code} value={country.code}>
                            {country.label}
                        </option>
                    ))}
                </Select>

                <Input
                    label="Telefoon"
                    type="tel"
                    optional
                    autoComplete="tel"
                    value={form.data.phone}
                    error={form.errors.phone}
                    onChange={(e) => form.setData('phone', e.target.value)}
                />
            </div>
        </CheckoutSection>
    );
}
