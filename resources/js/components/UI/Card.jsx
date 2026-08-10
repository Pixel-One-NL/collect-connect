/**
 * White surface with the border/rounding used across cart and checkout.
 *
 * @param {Object} props
 * @param {string} [props.className]
 * @param {React.ElementType} [props.as]
 * @param {React.ReactNode} props.children
 */
export default function Card({ as: Component = 'div', className = '', children, ...props }) {
    return (
        <Component
            className={`rounded-xl border border-gray-200 bg-white ${className}`.trim()}
            {...props}
        >
            {children}
        </Component>
    );
}
