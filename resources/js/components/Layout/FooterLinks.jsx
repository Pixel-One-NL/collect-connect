import {Link} from "@inertiajs/react";

/**
 * @param {Object} props
 * @param {string} props.title
 * @param {Array<{label: string, href: string}>} props.links
 */
export default function FooterLinks({title, links}) {
    return (
        <div>
            <h3 className="font-semibold text-lg mb-4">{title}</h3>

            <ul className="flex flex-col gap-2">
                {links.map((link) => (
                    <li key={link.label}>
                        <Link href={link.href} className="hover:text-primary">
                            {link.label}
                        </Link>
                    </li>
                ))}
            </ul>
        </div>
    );
}
