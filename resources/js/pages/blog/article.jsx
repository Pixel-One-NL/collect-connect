export default function Article({ article }) {
    return (
        <div className="container mx-auto my-6 space-y-8 px-4">
            <h1 className="text-3xl font-semibold">{article.title}</h1>

            <img className="max-h-90 w-full object-cover rounded-lg" src="https://images.unsplash.com/photo-1611604548018-d56bbd85d681?q=80&w=2340&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Image"/>

            <article className="prose" dangerouslySetInnerHTML={{__html: article.content}} />
        </div>
    );
}
