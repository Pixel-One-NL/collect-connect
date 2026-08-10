/**
 * Titled block in the checkout column.
 *
 * @param {Object} props
 * @param {string} props.title
 * @param {React.ReactNode} props.children
 */
export default function CheckoutSection({ title, children }) {
    return (
        <section className="space-y-4 border-b border-gray-200 pb-8 last:border-b-0 last:pb-0">
            <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
            {children}
        </section>
    );
}
