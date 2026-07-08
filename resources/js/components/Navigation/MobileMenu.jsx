import { useState } from "react";
import { CaretLeftIcon } from "@phosphor-icons/react/dist/csr/CaretLeft";
import MenuItem from "./MenuItem.jsx";

/**
 * Mobile menu: a single column where opening an item with children slides a new
 * page in from the right. Each sub page has a back button above its items.
 *
 * @param {Object} props
 * @param {Array<Object>} props.menu
 * @param {(href: string) => void} props.onNavigate
 */
export default function MobileMenu({ menu, onNavigate }) {
    const [levels, setLevels] = useState([{ items: menu, label: null }]);
    const [depth, setDepth] = useState(0);

    const open = (item) => {
        if (item.children?.length) {
            const next = levels.slice(0, depth + 1);
            next.push({ items: item.children, label: item.label });
            setLevels(next);
            setDepth(depth + 1);
            return;
        }
        if (item.href) {
            onNavigate(item.href);
        }
    };

    return (
        <div className="relative h-full overflow-hidden">
            <div
                className="flex h-full transition-transform duration-300 ease-out"
                style={{
                    width: `${levels.length * 100}%`,
                    transform: `translateX(-${depth * (100 / levels.length)}%)`,
                }}
            >
                {levels.map((level, levelIndex) => (
                    <div
                        key={levelIndex}
                        className="h-full overflow-y-auto px-4"
                        style={{ width: `${100 / levels.length}%` }}
                    >
                        {levelIndex > 0 && (
                            <button
                                onClick={() => setDepth(depth - 1)}
                                className="flex items-center gap-2 py-3 font-semibold text-sm uppercase text-gray-500 w-full cursor-pointer"
                            >
                                <CaretLeftIcon size={18} />
                                {level.label}
                            </button>
                        )}

                        <ul className="flex flex-col gap-1">
                            {level.items.map((item) => (
                                <MenuItem
                                    key={item.id}
                                    item={item}
                                    className="py-3"
                                    onClick={() => open(item)}
                                />
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
        </div>
    );
}
