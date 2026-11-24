import { CaretLeftIcon } from '@phosphor-icons/react/dist/csr/CaretLeft';
import { CaretRightIcon } from '@phosphor-icons/react/dist/csr/CaretRight';
import { Link } from '@inertiajs/react';

/**
 * Turns a paginator URL into a same-origin path, so a wrong scheme or host
 * from behind a proxy can never break client-side navigation.
 *
 * @param {string|null} url
 * @returns {string|null}
 */
function toPath(url) {
    if (!url) {
        return null;
    }
    const parsed = new URL(url, window.location.origin);
    return `${parsed.pathname}${parsed.search}`;
}

const baseClass = 'min-h-11 min-w-11 items-center justify-center gap-1 rounded-md border px-3 text-sm font-medium transition';
const idleClass = 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-accent';
const disabledClass = 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed';

/**
 * Pagination for Laravel paginator / resource-collection payloads.
 *
 * @param {Object} props
 * @param {Object} props.pagination - Resource collection (`meta`) or raw paginator.
 * @param {string} [props.label]
 * @param {boolean} [props.preserveScroll] - Keep the scroll position (for pages with several paginated lists).
 */
export default function Pagination({ pagination, label = 'Paginering', preserveScroll = false }) {
    const meta = pagination?.meta ?? pagination;
    if (!meta || meta.last_page <= 1) {
        return null;
    }

    const links = meta.links ?? [];
    const pages = links.slice(1, -1);
    const previous = toPath(links[0]?.url ?? null);
    const next = toPath(links.at(-1)?.url ?? null);

    return (
        <nav aria-label={label} className="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
            <div className="flex flex-wrap items-center justify-center gap-2">
                {previous ? (
                    <Link href={previous} preserveScroll={preserveScroll} rel="prev" className={`${baseClass} ${idleClass} inline-flex`}>
                        <CaretLeftIcon size={16} aria-hidden="true" /> Vorige
                    </Link>
                ) : (
                    <span aria-disabled="true" className={`${baseClass} ${disabledClass} inline-flex`}>
                        <CaretLeftIcon size={16} aria-hidden="true" /> Vorige
                    </span>
                )}

                {pages.map((link, index) => {
                    const href = toPath(link.url);
                    if (link.active) {
                        return (
                            <span key={index} aria-current="page" className={`${baseClass} inline-flex border-primary bg-primary text-white`}>
                                {link.label}
                            </span>
                        );
                    }
                    if (!href) {
                        return <span key={index} className="hidden px-1 text-gray-400 sm:inline" aria-hidden="true">…</span>;
                    }
                    return (
                        <Link key={index} href={href} preserveScroll={preserveScroll} aria-label={`Pagina ${link.label}`} className={`${baseClass} ${idleClass} hidden sm:inline-flex`}>
                            {link.label}
                        </Link>
                    );
                })}

                {next ? (
                    <Link href={next} preserveScroll={preserveScroll} rel="next" className={`${baseClass} ${idleClass} inline-flex`}>
                        Volgende <CaretRightIcon size={16} aria-hidden="true" />
                    </Link>
                ) : (
                    <span aria-disabled="true" className={`${baseClass} ${disabledClass} inline-flex`}>
                        Volgende <CaretRightIcon size={16} aria-hidden="true" />
                    </span>
                )}
            </div>
            <span className="text-sm text-gray-600">Pagina {meta.current_page} van {meta.last_page}</span>
        </nav>
    );
}
