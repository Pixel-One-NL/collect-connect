import Container from '../Container.jsx';
import InlineProduct from '../Shop/Products/InlineProduct.jsx';
import SectionHeading from '../UI/SectionHeading.jsx';

/**
 * Trending row. The products are curated in the admin panel and fall back to
 * the best-stocked products while nothing is curated.
 *
 * @param {Object} props
 * @param {Array<import("../../services/search.js").ProductResult>} props.products
 */
export default function TrendingProducts({ products = [] }) {
    if (products.length === 0) {
        return null;
    }

    return (
        <Container className="max-w-7xl my-8">
            <SectionHeading title="Trending" href="/onderdelen" divider />

            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                {products.map((product) => (
                    <InlineProduct key={product.id} product={product} />
                ))}
            </div>
        </Container>
    );
}
