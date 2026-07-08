import { ShoppingCartIcon } from '@phosphor-icons/react/dist/csr/ShoppingCart';
import Button from '../UI/Button.jsx';
import { useCart } from '../../hooks/useCart.js';

/**
 * @param {Object} props
 * @param {() => void} [props.onOpen]
 */
export default function CartIcon({ onOpen }) {
    const { count, total } = useCart();

    return (
        <Button onClick={onOpen}>
            <div className="relative">
                <ShoppingCartIcon size={24} />

                {count > 0 && (
                    <span className="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 p-1 min-w-5 bg-primary text-white leading-none text-[10px] rounded-full">
                        {count}
                    </span>
                )}
            </div>

            <span className="hidden sm:inline font-semibold text-sm uppercase">&euro; {total.toFixed(2)}</span>
        </Button>
    );
}
