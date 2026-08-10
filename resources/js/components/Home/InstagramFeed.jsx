import Container from '../Container.jsx';

/**
 * @param {Object} props
 * @param {string} [props.handle]
 * @param {string} [props.image]
 */
export default function InstagramFeed({
    handle = 'collect2connect',
    image = '/images/home/instagram-feed.png',
}) {
    return (
        <Container className="max-w-7xl my-8">
            <div className="mb-4 flex items-start gap-3 sm:items-center">
                <img
                    src="/images/home/instagram-icon.svg"
                    alt=""
                    aria-hidden="true"
                    className="size-8 shrink-0 sm:size-10"
                />

                <h2 className="min-w-0 text-2xl font-bold break-words text-gray-900 sm:text-3xl">
                    Tag jouw creaties met #{handle} op instagram
                </h2>
            </div>

            <img
                src={image}
                alt="Foto's van klanten op Instagram"
                className="h-auto w-full rounded-xl object-cover"
            />
        </Container>
    );
}
