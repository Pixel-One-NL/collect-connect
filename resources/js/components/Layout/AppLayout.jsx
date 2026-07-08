import { useEffect, useState } from 'react';
import Header from './Header';
import Footer from './Footer';
import CartDrawer from '../Cart/CartDrawer';

export default function AppLayout({ children }) {
    const [isCartOpen, setIsCartOpen] = useState(false);

    useEffect(() => {
        const handler = () => setIsCartOpen(true);
        window.addEventListener('cart:open', handler);
        return () => window.removeEventListener('cart:open', handler);
    }, []);

    return (
        <div className="flex flex-col min-h-screen bg-white">
            <Header onOpenCart={() => setIsCartOpen(true)} />

            <main className="flex-1">
                {children}
            </main>

            <Footer />

            <CartDrawer isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
        </div>
    );
}
