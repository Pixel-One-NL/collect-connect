import Container from '../Container.jsx';

const DEFAULT_BLOCKS = [
    {
        title: 'LEGO® onderdelen kopen',
        body: 'Bij Collect2Connect vind je losse LEGO® steentjes in honderden kleuren en vormen. Zoek op naam, kleur of onderdeelnummer en bestel precies de stenen die je mist voor jouw project.',
    },
    {
        title: 'Minifiguren en accessoires',
        body: 'Van ridders tot ruimtevaarders: onze minifiguren zijn stuk voor stuk gecontroleerd op compleetheid. Ook losse hoofddeksels, wapens en andere accessoires vind je hier terug.',
    },
    {
        title: 'Snel en zorgvuldig verzonden',
        body: 'Bestel je voor 16:00 uur, dan gaat je pakket dezelfde dag nog op de post. Elk onderdeel wordt met de hand gesorteerd en veilig verpakt, zodat je bestelling compleet aankomt.',
    },
    {
        title: 'Nieuw en gebruikt naast elkaar',
        body: 'Je kiest zelf tussen nieuwe steentjes en zorgvuldig geselecteerde gebruikte onderdelen. Zo bouw je jouw set af tegen de prijs die bij jou past.',
    },
];

/**
 * @param {Object} props
 * @param {Array<{title: string, body: string}>} [props.blocks]
 */
export default function SeoSection({ blocks = DEFAULT_BLOCKS }) {
    if (blocks.length === 0) {
        return null;
    }

    return (
        <Container className="max-w-7xl my-8">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 md:gap-x-12">
                {blocks.map((block) => (
                    <section key={block.title} className="min-w-0">
                        <h2 className="mb-2 text-xl font-semibold break-words text-gray-900 sm:text-2xl">
                            {block.title}
                        </h2>
                        <p className="text-base leading-relaxed text-gray-600 sm:text-lg">{block.body}</p>
                    </section>
                ))}
            </div>
        </Container>
    );
}
