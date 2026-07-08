export default function StockIndicator({ stock }) {
    return (
        <div className="text-xs inline-flex gap-2 items-center">
            <div
                className={`rounded-full w-2.5 h-2.5
                    ${stock < 1 && 'bg-red-500'}
                    ${stock >= 1 && stock <= 100 && 'bg-yellow-500'}
                    ${stock > 100 && 'bg-green-600'}
                `}
            />

            {stock < 1 && (
                <span className="text-red-500">Niet op voorraad</span>
            )}

            {stock >= 1 && stock <= 100 && (
                <span className="text-yellow-500">Voorraad: {stock}</span>
            )}

            {stock > 100 && (
                <span className="text-green-600">Voorraad: 100+</span>
            )}
        </div>
    );
}
