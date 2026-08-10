import { CheckCircleIcon } from '@phosphor-icons/react/dist/csr/CheckCircle';

/**
 * Selectable card used for shipping and payment choices. Wraps a real radio
 * input so keyboard and screen reader behaviour stays intact.
 *
 * @param {Object} props
 * @param {string} props.name - Radio group name.
 * @param {boolean} props.checked
 * @param {() => void} props.onSelect
 * @param {React.ReactNode} props.children
 * @param {string} [props.className]
 */
export default function RadioCard({ name, checked, onSelect, children, className = '' }) {
    return (
        <label
            className={`relative flex cursor-pointer rounded-xl border bg-white p-4 transition ${
                checked
                    ? 'border-primary ring-1 ring-primary'
                    : 'border-gray-200 hover:border-gray-300'
            } ${className}`.trim()}
        >
            <input
                type="radio"
                name={name}
                checked={checked}
                onChange={onSelect}
                className="sr-only"
            />

            <div className="flex-1">{children}</div>

            {checked && (
                <CheckCircleIcon
                    size={20}
                    weight="fill"
                    className="absolute right-3 top-3 text-primary"
                />
            )}
        </label>
    );
}
