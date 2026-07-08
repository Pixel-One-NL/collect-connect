import {ShoppingCartIcon} from "@phosphor-icons/react/dist/csr/ShoppingCart";

export default function EmptyCart() {
    return (
        <div className="space-y-4 text-center my-auto">
            <div className="bg-gray-200 text-gray-400 aspect-square rounded-full w-24 h-24 inline-flex items-center justify-center">
                <ShoppingCartIcon size={48} />
            </div>

            <h3 className="text-lg font-semibold text-gray-900">Je winkelmand is leeg</h3>
        </div>
    );
}
