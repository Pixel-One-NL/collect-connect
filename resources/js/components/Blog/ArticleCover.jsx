import { NewspaperIcon } from '@phosphor-icons/react/dist/csr/Newspaper';

const studPattern = {
    backgroundImage: 'radial-gradient(circle at center, rgba(255,255,255,0.35) 0 9px, rgba(255,255,255,0.12) 9px 11px, transparent 11px)',
    backgroundSize: '36px 36px',
};

/**
 * Article cover image, or a brick-stud gradient when the article has none.
 *
 * @param {Object} props
 * @param {string|null} props.image
 * @param {string} props.alt
 * @param {string} [props.className] - Sizing / aspect classes for the wrapper.
 * @param {boolean} [props.zoomOnHover] - Zoom when a parent `.group` is hovered.
 * @param {'eager'|'lazy'} [props.loading]
 */
export default function ArticleCover({ image, alt, className = '', zoomOnHover = false, loading = 'lazy' }) {
    const zoomClass = zoomOnHover ? 'transition duration-500 ease-out group-hover:scale-105 motion-reduce:transition-none motion-reduce:group-hover:scale-100' : '';

    return (
        <div className={`relative overflow-hidden bg-accent ${className}`}>
            {image ? (
                <img src={image} alt={alt} loading={loading} className={`h-full w-full object-cover ${zoomClass}`} />
            ) : (
                <div aria-hidden="true" className={`flex h-full w-full items-center justify-center bg-gradient-to-br from-primary via-secondary to-accent ${zoomClass}`}>
                    <div className="absolute inset-0" style={studPattern} />
                    <NewspaperIcon weight="duotone" className="relative size-14 text-white/90 drop-shadow" />
                </div>
            )}
        </div>
    );
}
