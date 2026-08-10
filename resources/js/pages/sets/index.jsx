import { Link, router } from '@inertiajs/react';
import Container from '../../components/Container';
import InlineSet from '../../components/Shop/Sets/InlineSet';

export default function SetsIndex({ sets, query }) {
    const items = sets?.data ?? [];

    return (
        <Container className="max-w-7xl my-8">
            <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <h1 className="text-3xl font-bold">Sets</h1>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        router.get('/sets', { q: e.target.q.value }, { preserveState: true });
                    }}
                >
                    <input
                        name="q"
                        defaultValue={query ?? ''}
                        placeholder="Zoek setnummer of naam..."
                        className="border border-gray-200 rounded-lg px-4 py-2 w-full md:w-80"
                    />
                </form>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                {items.map((set) => {
                    const s = set.data ?? set;
                    return <InlineSet key={s.id} set={s} />;
                })}
            </div>

            {items.length === 0 && (
                <p className="text-gray-500">Geen sets gevonden.</p>
            )}
        </Container>
    );
}
