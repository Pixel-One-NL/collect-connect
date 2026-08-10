import { Link } from '@inertiajs/react';
import { CaretRightIcon } from '@phosphor-icons/react/dist/csr/CaretRight';

/**
 * @param {Object} props
 * @param {{name: string, url: string}} props.category
 * @param {string} [props.description]
 * @param {string} [props.image]
 */
export default function CategoryCard({
    category,
    description = 'De klassieke steentjes die de basis vormen voor ieder project.',
    image = '/images/home/category-bricks.png',
}) {
    return (
        <Link
            href={category.url}
            className="group flex h-full flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 hover:shadow-sm"
        >
            <img
                src={image}
                alt=""
                aria-hidden="true"
                className="aspect-[4/3] w-full rounded-lg object-cover"
            />

            <div className="min-w-0">
                <h3 className="text-lg font-semibold break-words text-gray-900 group-hover:underline sm:text-xl">
                    {category.name}
                </h3>
                <p className="mt-1 text-sm text-gray-600 sm:text-base">{description}</p>
            </div>

            <span className="mt-auto inline-flex items-center gap-1 text-sm font-medium text-gray-900 sm:text-base">
                Bekijk alle producten
                <CaretRightIcon size={18} />
            </span>
        </Link>
    );
}
