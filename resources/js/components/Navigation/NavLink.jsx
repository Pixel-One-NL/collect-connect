/**
 * @param {Object} props
 * @param {string} props.href
 * @param {string} props.children
 * @param {boolean} [props.active]
 */
export default function NavLink({ href, children, active = false }) {
  return (
    <a
      href={href}
      className={`text-sm font-semibold uppercase px-4 py-2 transition ${active && 'text-primary border-b-2 border-primary'}`}
    >
      {children}
    </a>
  );
}
