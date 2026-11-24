import { Head } from '@inertiajs/react';
import Container from '../../components/Container';
import ArticleCard from '../../components/Blog/ArticleCard';
import Pagination from '../../components/UI/Pagination';

export default function BlogIndex({ articles }) {
    const items = articles.data ?? [];
    const isFirstPage = (articles.meta?.current_page ?? 1) === 1;
    const [featured, ...rest] = isFirstPage ? items : [null, ...items];

    return (
        <>
            <Head title="Blog" />
            <section className="border-brick-bottom-accent bg-accent">
                <Container className="max-w-6xl py-12 lg:py-16">
                    <p className="text-sm font-semibold uppercase tracking-wide text-primary">Blog</p>
                    <h1 className="mt-2 text-3xl font-bold text-gray-900 lg:text-5xl">Nieuws, tips &amp; inspiratie</h1>
                    <p className="mt-4 max-w-2xl text-lg text-gray-600">Verhalen over bouwen, verzamelen en de nieuwste vondsten uit onze collectie.</p>
                </Container>
            </section>

            <Container className="my-10 max-w-6xl space-y-10">
                {items.length === 0 && (
                    <div className="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-600">
                        Er zijn nog geen artikelen gepubliceerd.
                    </div>
                )}

                {featured && <ArticleCard article={featured} featured />}

                {rest.length > 0 && (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {rest.map(article => <ArticleCard key={article.id} article={article} />)}
                    </div>
                )}

                <Pagination pagination={articles} label="Blog paginering" />
            </Container>
        </>
    );
}
