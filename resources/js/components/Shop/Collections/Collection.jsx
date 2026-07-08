import {Link} from "@inertiajs/react";

export default function Collection({ collection }) {
    return (
        <Link href="#" className="group inline-flex flex-col text-center items-center gap-2 group max-w-28 sm:max-w-36 hyphens-auto wrap-anywhere">
            <img className="rounded-full w-28 sm:w-36" src="https://picsum.photos/200" alt="Collection" />
            <h3 className="group-hover:underline text-sm">CollectionCollectionCollection Collection</h3>
        </Link>
    );
}
