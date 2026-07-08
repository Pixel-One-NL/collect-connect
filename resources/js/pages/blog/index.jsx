import {Link} from "@inertiajs/react";

export default function BlogIndex({ articles }) {
    return (
        <>
            {articles.data.map(article => (
                <Link href={`/blog/${article.slug}`} key={article.id}>
                    <h2>{article.title}</h2>
                </Link>
            ))}
        </>
    );
}
