import Container from '../../components/Container';
import SingleInlineProduct from '../../components/Shop/Products/SingleInlineProduct.jsx';

/**
 * @param {Object} props
 * @param {{ data: { id: number, set_num: string, name: string, year: number, num_parts: number, image: string|null } }} props.set
 * @param {Array} props.in_stock_parts
 * @param {Array} props.out_of_stock_parts
 */
export default function SetPage({ set: { data: set }, in_stock_parts, out_of_stock_parts }) {
    return (
        <Container className="max-w-250 my-8">
            <div className="py-10 grid grid-cols-1 md:grid-cols-5 gap-10 md:shadow-[0_0px_10px_rgba(0,0,0,0.1)] md:px-6 rounded-xl">
                <div className="rounded-xl overflow-hidden flex items-center justify-center aspect-square md:col-span-2 max-h-60 md:max-h-none mx-auto">
                    {set.image ? (
                        <img
                            src={set.image}
                            alt={set.name}
                            className="object-contain w-full h-full"
                        />
                    ) : (
                        <div className="w-full h-full bg-gray-100 rounded-xl" />
                    )}
                </div>

                <div className="flex flex-col gap-4 md:col-span-3">
                    <div>
                        <p className="text-sm text-gray-400 mb-1">{set.set_num}</p>
                        <h1 className="text-2xl font-bold text-gray-900">{set.name}</h1>
                    </div>

                    <dl className="flex flex-col gap-2">
                        <div className="flex items-center gap-2">
                            <dt className="text-sm text-gray-500 w-32">Jaar</dt>
                            <dd className="text-sm font-medium text-gray-900">{set.year}</dd>
                        </div>
                        <div className="flex items-center gap-2">
                            <dt className="text-sm text-gray-500 w-32">Aantal onderdelen</dt>
                            <dd className="text-sm font-medium text-gray-900">{set.num_parts}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {in_stock_parts.length > 0 && (
                <div className="mt-10">
                    <h2 className="text-xl font-semibold text-gray-900">
                        Beschikbaar
                        <span className="ml-2 text-base font-normal text-gray-400">
                            ({in_stock_parts.length})
                        </span>
                    </h2>

                    <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        {in_stock_parts.map((part) => (
                            <SingleInlineProduct
                                key={part.id ?? part.lego_number}
                                product={part}
                                quantityInSet={part.quantity_in_set}
                            />
                        ))}
                    </div>
                </div>
            )}

            {out_of_stock_parts.length > 0 && (
                <div className="mt-10">
                    <h2 className="text-xl font-semibold text-gray-900">
                        Niet op voorraad
                        <span className="ml-2 text-base font-normal text-gray-400">
                            ({out_of_stock_parts.length})
                        </span>
                    </h2>

                    <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        {out_of_stock_parts.map((part, i) => (
                            <SingleInlineProduct
                                key={part.id ?? `${part.lego_number}-${i}`}
                                product={part}
                                quantityInSet={part.quantity_in_set}
                            />
                        ))}
                    </div>
                </div>
            )}
        </Container>
    );
}
