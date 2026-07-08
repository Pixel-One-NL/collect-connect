import { createInertiaApp } from '@inertiajs/react';

import AppLayout from './components/Layout/AppLayout';

import '../css/app.css';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.jsx', { eager: true });
        const page = pages[`./pages/${name}.jsx`];
        page.default.layout ??= (children) => <AppLayout>{children}</AppLayout>;
        return page;
    },
});
