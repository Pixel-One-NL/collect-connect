import { Link } from "@inertiajs/react";
import { Tooltip } from "react-tooltip";

import "react-tooltip/dist/react-tooltip.css";
import StockIndicator from "./StockIndicator.jsx";

export default function ColorPreview({ product }) {
    const tooltipId = `color-preview-${product.id}`;

    return (
        <>
            <Link
                href={`/products/${product.id}`}
                data-tooltip-id={tooltipId}
                className="inline-flex"
                aria-label={product.color.name}
            >
                {product.color?.hex ? (
                    <span
                        className="inline-block h-6 w-6 rounded-full border border-gray-200"
                        style={{ backgroundColor: `#${product.color.hex}` }}
                    />
                ) : (
                    <span className="inline-block h-6 w-6 rounded-full border border-gray-200 bg-gray-100" />
                )}
            </Link>

            <Tooltip id={tooltipId} place="top" className="text-xs" opacity={1}>
                {product.image && (
                    <img src={product.image} className="opacity-100 w-full max-w-24 mx-auto mb-2" />
                )}

                <p>{product.color.name}</p>
                <StockIndicator stock={product.stock} />
            </Tooltip>
        </>
    );
}
