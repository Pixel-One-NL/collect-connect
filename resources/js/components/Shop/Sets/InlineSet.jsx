import { Link } from "@inertiajs/react";

/**
 * Compact set card for search results.
 *
 * @param {Object} props
 * @param {import("../../../services/search.js").SetResult} props.set
 */
export default function InlineSet({ set }) {
    return (
        <Link
            href={set.url}
            className="group flex flex-col gap-2 rounded-lg border border-gray-200 p-3 transition hover:border-gray-300 hover:shadow-sm"
        >
            {set.image ? (
                <img
                    src={set.image}
                    alt={set.name}
                    className="aspect-square w-full rounded-md object-contain"
                />
            ) : (
                <div className="aspect-square w-full rounded-md bg-gray-100" />
            )}

            <div className="min-w-0">
                <p className="text-xs text-gray-400">{set.set_num}</p>
                <h3 className="truncate text-sm font-medium text-gray-900 group-hover:underline">
                    {set.name}
                </h3>
                <p className="text-xs text-gray-500">
                    {set.year} &middot; {set.num_parts} onderdelen
                </p>
            </div>
        </Link>
    );
}
