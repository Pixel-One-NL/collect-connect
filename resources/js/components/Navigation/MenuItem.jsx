import { CaretRightIcon } from "@phosphor-icons/react/dist/csr/CaretRight";

/**
 * A single navigation row: label plus a caret when it has children. Shared by
 * the desktop and mobile menus.
 *
 * @param {Object} props
 * @param {Object} props.item - Menu node ({ label, children?, ... }).
 * @param {boolean} [props.active] - Highlight as the opened item.
 * @param {(e: React.MouseEvent) => void} props.onClick
 * @param {string} [props.className] - Extra classes (e.g. vertical padding).
 */
export default function MenuItem({ item, active = false, onClick, className = "" }) {
    return (
        <li>
            <button
                onClick={onClick}
                className={`w-full flex items-center justify-between gap-2 px-3 rounded-md text-left transition cursor-pointer ${
                    active ? "bg-gray-100 text-primary font-semibold" : "hover:bg-gray-100"
                } ${className}`}
            >
                <span>{item.label}</span>
                {item.children?.length > 0 && <CaretRightIcon size={16} className="shrink-0" />}
            </button>
        </li>
    );
}
