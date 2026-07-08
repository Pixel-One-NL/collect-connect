import ColorPreview from "./ColorPreview.jsx";

export default function ProductSiblingsOverview({ productSiblings }) {
    return (
        <div>
            <h3 className="text-lg font-semibold mb-2">Andere kleuren:</h3>

            <div className="flex flex-wrap gap-2">
                {productSiblings.map((sibling) => (
                    <ColorPreview key={sibling.id} product={sibling} />
                ))}
            </div>
        </div>
    );
}
