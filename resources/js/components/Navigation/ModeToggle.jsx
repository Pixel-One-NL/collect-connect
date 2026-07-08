import { ListIcon } from "@phosphor-icons/react/dist/csr/List";
import { MagnifyingGlassIcon } from "@phosphor-icons/react/dist/csr/MagnifyingGlass";

/**
 * Segmented switch toggling the overlay between menu mode and search mode.
 *
 * @param {Object} props
 * @param {"menu"|"search"} props.mode
 * @param {(mode: "menu"|"search") => void} props.onChange
 */
export default function ModeToggle({ mode, onChange }) {
    const option = (value, Icon) => {
        const isActive = mode === value;

        return (
            <button
                type="button"
                onClick={() => onChange(value)}
                className={`p-2 rounded-full transition cursor-pointer ${
                    isActive ? "bg-white text-primary shadow-sm" : "text-gray-400 hover:text-gray-600"
                }`}
            >
                <Icon size={20} />
            </button>
        );
    };

    return (
        <div className="flex items-center gap-1 bg-gray-100 rounded-full p-1 shrink-0">
            {option("menu", ListIcon)}
            {option("search", MagnifyingGlassIcon)}
        </div>
    );
}
