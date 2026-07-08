import FooterLinks from './FooterLinks';

export default function Footer() {
    const popularCategories = [
        {label: 'Harry Potter', href: '#'},
        {label: 'Avengers', href: '#'},
        {label: 'City', href: '#'},
        {label: 'Technics', href: '#'},
        {label: 'Star Wars', href: '#'},
        {label: 'Mario', href: '#'},
    ];

    const customerService = [
        {label: 'Mijn account', href: '#'},
        {label: 'Veelgestelde vragen', href: '#'},
        {label: 'Over ons', href: '#'},
        {label: 'Contact', href: '#'},
    ];

    return (
        <footer className="relative bg-accent py-10 pb-8 px-8 min-h-62.5 border-brick-top-accent">
            <img src="/images/elements/alien.png" alt="" className="absolute bottom-full right-8 translate-y-1/2 -rotate-12 hidden lg:block pointer-events-none" />

            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <img src="/images/coco-logo-small.svg" alt="Collect2Connect" className="w-full max-w-60 hidden md:block" />
                <img src="/images/coco-logo.svg" alt="Collect2Connect" className="w-full max-w-60 md:hidden" />

                <FooterLinks title="Popular categories" links={popularCategories} />
                <FooterLinks title="Customer service" links={customerService} />
            </div>

            <div className="flex items-center gap-2 mt-8">
                <img className="h-8" src="/images/payment-methods/ideal-wero.svg" alt="iDEAL / Wero" />
                <img className="h-8" src="/images/payment-methods/paypal.svg" alt="PayPal" />
                <img className="h-8" src="/images/payment-methods/visa.svg" alt="Visa" />
                <img className="h-8" src="/images/payment-methods/mastercard.svg" alt="Mastercard" />
            </div>

            <hr className="border-gray-300 my-8" />

            <p className="text-sm text-gray-500">&copy; {new Date().getFullYear()} Collect2Connect. All rights reserved.</p>
            <p className="text-sm text-gray-500">LEGO, the LEGO logo, the Minifigure, DUPLO, MINDSTORMS and LEGENDS OF CHIMA are trademarks of the LEGO Group. © 2025 The LEGO Group.</p>
        </footer>
    );
}
