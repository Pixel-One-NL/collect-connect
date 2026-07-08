import { useLayoutEffect, useRef, useState } from "react";
import { CaretLeftIcon } from "@phosphor-icons/react/dist/csr/CaretLeft";
import Button from "../UI/Button.jsx";
import MenuItem from "./MenuItem.jsx";

const COLUMN_WIDTH = 288; // w-72 (18rem)

/**
 * Desktop mega menu rendered as Miller columns, driven by clicks. Clicking an
 * item with children opens a new column to the right and focuses it; other
 * columns dim. Clicking a dimmed (parent) column re-focuses it; the back button
 * collapses a column to its parent. Once columns overflow the container, the
 * deepest column is pinned to the right edge (no manual scrolling).
 *
 * @param {Object} props
 * @param {Array<Object>} props.menu
 * @param {(href: string) => void} props.onNavigate
 */
export default function MegaMenu({ menu, onNavigate }) {
    // path[i] = id of the item selected in column i that spawned column i + 1.
    const [path, setPath] = useState([]);
    const [activeIndex, setActiveIndex] = useState(0);
    const [containerWidth, setContainerWidth] = useState(0);
    const containerRef = useRef(null);

    // Track the container width to know when columns overflow.
    useLayoutEffect(() => {
        const container = containerRef.current;
        if (!container) {
            return;
        }

        const measure = () => setContainerWidth(container.clientWidth);

        measure();
        const observer = new ResizeObserver(measure);
        observer.observe(container);
        return () => observer.disconnect();
    }, []);

    // Derive the visible columns from the current path.
    const columns = [{ items: menu, parentLabel: null }];
    let level = menu;
    for (let i = 0; i < path.length; i++) {
        const selected = level.find((item) => item.id === path[i]);
        if (!selected?.children?.length) {
            break;
        }
        level = selected.children;
        columns.push({ items: level, parentLabel: selected.label });
    }

    // Pin the deepest column to the right edge once the columns overflow; the
    // row stays put (no shifting) as long as everything still fits.
    const overflow = Math.max(0, columns.length * COLUMN_WIDTH - containerWidth);

    const selectItem = (colIndex, item) => {
        if (item.children?.length) {
            setPath([...path.slice(0, colIndex), item.id]);
            setActiveIndex(colIndex + 1);
            return;
        }
        if (item.href) {
            onNavigate(item.href);
        }
    };

    const handleItemClick = (e, colIndex, item) => {
        e.stopPropagation();
        selectItem(colIndex, item);
    };

    // Focus a column and close everything after it (used for background clicks
    // and, via the back button, to collapse a column to its parent).
    const focusColumn = (colIndex) => {
        setPath(path.slice(0, colIndex));
        setActiveIndex(colIndex);
    };

    return (
        <div ref={containerRef} className="h-full overflow-hidden">
            <div
                className="flex h-full transition-transform duration-300 ease-out"
                style={{ transform: `translateX(-${overflow}px)` }}
            >
                {columns.map((column, colIndex) => {
                    const isActive = colIndex === activeIndex;
                    const selectedId = path[colIndex];

                    return (
                        <div
                            key={colIndex}
                            onClick={() => focusColumn(colIndex)}
                            style={{ width: COLUMN_WIDTH }}
                            className={`shrink-0 border-r border-gray-200 p-3 transition-opacity duration-200 ${
                                isActive ? "opacity-100" : "opacity-40"
                            }`}
                        >
                            {column.parentLabel && (
                                <div className="flex items-center gap-1 mb-2">
                                    <Button
                                        iconOnly
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            focusColumn(colIndex - 1);
                                        }}
                                    >
                                        <CaretLeftIcon size={18} />
                                    </Button>
                                    <span className="font-semibold text-sm uppercase text-gray-500">
                                        {column.parentLabel}
                                    </span>
                                </div>
                            )}

                            <ul className="flex flex-col gap-1">
                                {column.items.map((item) => (
                                    <MenuItem
                                        key={item.id}
                                        item={item}
                                        active={item.id === selectedId}
                                        className="py-2"
                                        onClick={(e) => handleItemClick(e, colIndex, item)}
                                    />
                                ))}

                                <hr className="border-gray-200 my-2" />

                                <MenuItem
                                    item={{ label: 'Blog' }}
                                    className="py-2"
                                    onClick={() => onNavigate('/blog')}
                                />

                                <MenuItem
                                    item={{ label: 'Contact' }}
                                    className="py-2"
                                    onClick={() => onNavigate('/contact')}
                                />
                            </ul>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
