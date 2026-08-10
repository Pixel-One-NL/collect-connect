/**
 * Labelled select with inline validation message.
 *
 * @param {Object} props
 * @param {string} props.label
 * @param {string} [props.error] - Message from Inertia's form.errors.
 * @param {string} [props.className] - Extra classes on the wrapper.
 * @param {React.ReactNode} props.children - <option> elements.
 */
export default function Select({ label, error, className = '', id, children, ...props }) {
    const selectId = id ?? `field-${label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;

    return (
        <div className={`space-y-1.5 ${className}`.trim()}>
            <label htmlFor={selectId} className="block text-sm text-gray-600">
                {label}
            </label>

            <select
                id={selectId}
                aria-invalid={error ? 'true' : undefined}
                className={`w-full rounded-lg border bg-white px-3 py-2 outline-none transition focus:border-primary focus:ring-1 focus:ring-primary ${
                    error ? 'border-red-400' : 'border-gray-200'
                }`}
                {...props}
            >
                {children}
            </select>

            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
