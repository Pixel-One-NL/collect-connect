import Price from '../Shop/Price';
import RadioCard from '../UI/RadioCard';
import CheckoutSection from './CheckoutSection';

/**
 * @param {Object} props
 * @param {Array<{ id: number, name: string, price_cents: number, track_trace: boolean }>} props.methods
 * @param {number} props.selectedId
 * @param {(method: Object) => void} props.onSelect
 * @param {string} [props.error]
 */
export default function ShippingMethodCards({ methods, selectedId, onSelect, error }) {
    return (
        <CheckoutSection title="Leveringsmethode">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {methods.map((method) => (
                    <RadioCard
                        key={method.id}
                        name="shipping_method"
                        checked={selectedId === method.id}
                        onSelect={() => onSelect(method)}
                    >
                        <p className="pr-6 font-medium text-gray-900">{method.name}</p>
                        <p className="mt-0.5 text-sm text-gray-500">
                            {method.track_trace ? 'Met track & trace' : 'Zonder track & trace'}
                        </p>
                        <p className="mt-3 text-sm font-medium text-gray-900">
                            <Price price={method.price_cents} />
                        </p>
                    </RadioCard>
                ))}
            </div>

            {error && <p className="text-sm text-red-600">{error}</p>}
        </CheckoutSection>
    );
}
