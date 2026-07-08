export default function Price({ price }) {
    price = price / 100;

    return (
        <>
            {price.toLocaleString('nl-NL', {
                style: 'currency',
                currency: 'EUR',
            })}
        </>
    );
}
