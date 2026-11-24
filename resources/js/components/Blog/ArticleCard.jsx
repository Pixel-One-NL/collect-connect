import { Link } from '@inertiajs/react';
import { ArrowRightIcon } from '@phosphor-icons/react/dist/csr/ArrowRight';
import ArticleCover from './ArticleCover';
import ArticleDate from './ArticleDate';

/**
 * Blog overview card. `featured` renders the large side-by-side variant.
 *
 * @param {Object} props
 * @param {Object} props.article - ArticleResource payload.
 * @param {boolean} [props.featured]
 */
export default function ArticleCard({ article, featured = false }) {
    return (
        <article className={`group relative flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-lg motion-reduce:hover:translate-y-0 ${featured ? 'flex-col lg:grid lg:grid-cols-5' : 'flex-col'}`}>
            <ArticleCover
                image={article.image}
                alt=""
                zoomOnHover
                loading={featured ? 'eager' : 'lazy'}
                className={featured ? 'aspect-[16/9] lg:col-span-3 lg:aspect-auto lg:min-h-80' : 'aspect-[16/9]'}
            />
            <div className={`flex flex-1 flex-col gap-3 ${featured ? 'p-6 lg:col-span-2 lg:justify-center lg:p-10' : 'p-5'}`}>
                {featured && <span className="w-fit rounded-full bg-accent px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">Nieuwste artikel</span>}
                <ArticleDate date={article.published_at} />
                <h2 className={`font-bold text-gray-900 transition group-hover:text-primary ${featured ? 'text-2xl lg:text-3xl' : 'text-lg'}`}>
                    <Link href={article.url} className="after:absolute after:inset-0 focus-visible:outline-none">
                        {article.title}
                    </Link>
                </h2>
                {article.excerpt && <p className={`text-gray-600 ${featured ? 'line-clamp-4' : 'line-clamp-3'}`}>{article.excerpt}</p>}
                <span className="mt-auto inline-flex items-center gap-1.5 pt-2 text-sm font-semibold text-primary">
                    Lees verder
                    <ArrowRightIcon className="size-4 transition group-hover:translate-x-1" aria-hidden="true" />
                </span>
            </div>
        </article>
    );
}
