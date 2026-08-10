import { Link } from '@inertiajs/react';
import { CaretRightIcon } from '@phosphor-icons/react/dist/csr/CaretRight';

/**
 * Heading for a content row, with an optional link to the full overview.
 *
 * @param {Object} props
 * @param {React.ReactNode} props.title
 * @param {string} [props.href] - Overview link; the link is hidden without it.
 * @param {string} [props.linkLabel]
 * @param {boolean} [props.divider] - Draws the rule used on listing rows.
 * @param {string} [props.className]
 */
export default function SectionHeading({
    title,
    href,
    linkLabel = 'Naar overzicht',
    divider = false,
    className = '',
}) {
    return (
        <div
            className={`mb-4 flex flex-wrap items-end justify-between gap-x-4 gap-y-2 ${divider ? 'border-b border-gray-200 pb-4' : ''} ${className}`.trim()}
        >
            <h2 className="min-w-0 text-2xl font-bold text-gray-900 sm:text-3xl">{title}</h2>

            {href && (
                <Link
                    href={href}
                    className="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-secondary hover:underline sm:text-base"
                >
                    {linkLabel}
                    <CaretRightIcon size={18} />
                </Link>
            )}
        </div>
    );
}
