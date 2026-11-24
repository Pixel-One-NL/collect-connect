import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon } from '@phosphor-icons/react/dist/csr/ArrowLeft';
import Container from '../../components/Container';
import ArticleCover from '../../components/Blog/ArticleCover';
import ArticleDate from '../../components/Blog/ArticleDate';

export default function Article({ article: resource }) {
    const article = resource.data ?? resource;

    return (
        <Container className="my-10 max-w-5xl">
            <Head title={article.meta_title || article.title}>
                <meta name="description" content={article.meta_description || article.excerpt || ''} />
                {article.image && <meta property="og:image" content={article.image} />}
            </Head>

            <div className="mx-auto max-w-3xl">
                <Link href="/blog" className="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-primary">
                    <ArrowLeftIcon className="size-4" aria-hidden="true" />
                    Terug naar blog
                </Link>
                <header className="mt-6 space-y-4">
                    <ArticleDate date={article.published_at} />
                    <h1 className="text-3xl font-bold leading-tight text-gray-900 sm:text-4xl lg:text-5xl">{article.title}</h1>
                </header>
            </div>

            {article.image && (
                <ArticleCover image={article.image} alt={article.title} loading="eager" className="mt-8 aspect-[2/1] rounded-xl shadow-sm" />
            )}

            <article
                className="prose prose-lg mx-auto mt-10 max-w-3xl prose-headings:text-gray-900 prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl"
                dangerouslySetInnerHTML={{ __html: article.content }}
            />
        </Container>
    );
}
