import { CalendarBlankIcon } from '@phosphor-icons/react/dist/csr/CalendarBlank';

const formatter = new Intl.DateTimeFormat('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' });

/**
 * @param {Object} props
 * @param {string|null} props.date - ISO 8601 date.
 * @param {string} [props.className]
 */
export default function ArticleDate({ date, className = '' }) {
    if (!date) {
        return null;
    }

    return (
        <time dateTime={date} className={`inline-flex items-center gap-1.5 text-sm text-gray-500 ${className}`}>
            <CalendarBlankIcon className="size-4" aria-hidden="true" />
            {formatter.format(new Date(date))}
        </time>
    );
}
