import Container from '../../components/Container';

export default function CmsPage({ page }) {
    const blocks = page.blocks ?? [];

    return (
        <Container className="max-w-4xl my-10 space-y-8">
            <h1 className="text-3xl font-bold">{page.title}</h1>
            {blocks.map((row, rowIndex) => (
                <div key={rowIndex} className={`grid gap-4 grid-cols-1 md:grid-cols-${row.columns?.length || 1}`}>
                    {(row.columns ?? []).map((col, colIndex) => (
                        <div key={colIndex} className="space-y-3">
                            {(col.blocks ?? []).map((block, blockIndex) => {
                                if (block.type === 'heading') {
                                    return <h2 key={blockIndex} className="text-xl font-semibold">{block.text}</h2>;
                                }
                                if (block.type === 'html') {
                                    return <div key={blockIndex} className="prose max-w-none" dangerouslySetInnerHTML={{ __html: block.html }} />;
                                }
                                if (block.type === 'image') {
                                    return <img key={blockIndex} src={block.src} alt={block.alt ?? ''} className="rounded-lg max-w-full" />;
                                }
                                return <p key={blockIndex}>{block.text}</p>;
                            })}
                        </div>
                    ))}
                </div>
            ))}
        </Container>
    );
}
