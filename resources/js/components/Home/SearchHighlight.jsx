import { ListMagnifyingGlassIcon } from '@phosphor-icons/react/dist/csr/ListMagnifyingGlass';

/**
 * Full width search band with the ribbed brick edges. Clicking it opens the
 * shared search overlay owned by the header, through the same window-event
 * pattern the cart drawer uses.
 */
export default function SearchHighlight() {
    const openSearch = (event) => {
        window.dispatchEvent(
            new CustomEvent('search:open', {
                detail: { origin: event.currentTarget.getBoundingClientRect() },
            }),
        );
    };

    return (
        <section className="my-20 border-brick-y-secondary bg-secondary py-20 text-center px-8">
            <img
                src="/images/elements/rocket.png"
                alt=""
                className="absolute top-1/2 -left-10 -translate-y-1/2 rotate-30 hidden lg:block pointer-events-none z-10 max-h-72 lg:max-h-96"
            />

            <button
                onClick={openSearch}
                className="px-8 py-4 bg-white rounded-lg w-full max-w-4xl inline-flex items-center gap-4 cursor-pointer"
            >
                <ListMagnifyingGlassIcon size={28} className="shrink-0" />
                <span className="min-w-0 truncate text-left">
                    Zoek op naam, onderdelen, minifiguren, lego-nummer...
                </span>
            </button>
        </section>
    );
}
