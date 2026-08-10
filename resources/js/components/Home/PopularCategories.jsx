import Container from '../Container.jsx';
import SectionHeading from '../UI/SectionHeading.jsx';
import CategoryCard from './CategoryCard.jsx';

/**
 * @param {Object} props
 * @param {Array<{id: number, name: string, url: string}>} props.categories
 */
export default function PopularCategories({ categories = [] }) {
    if (categories.length === 0) {
        return null;
    }

    return (
        <Container className="max-w-7xl my-8">
            <SectionHeading
                title={<>Meest populaire LEGO&reg; parts categorie&euml;n</>}
                href="/onderdelen"
                linkLabel="Alle categorieën"
            />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {categories.map((category) => (
                    <CategoryCard key={category.id} category={category} />
                ))}
            </div>
        </Container>
    );
}
