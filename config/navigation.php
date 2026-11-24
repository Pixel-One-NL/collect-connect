<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront Navigation Menu
    |--------------------------------------------------------------------------
    |
    | Items with a `source` get their children generated from the catalog
    | (see App\Services\NavigationMenu). The `href` of such an item is shown
    | as an "Alle ..." link at the top of its sub menu.
    |
    */

    'menu' => [
        [
            'id' => 'parts',
            'label' => 'Onderdelen',
            'href' => '/onderdelen',
            'source' => 'part_categories',
        ],
        [
            'id' => 'minifigs',
            'label' => 'Minifiguren',
            'href' => '/minifiguren',
        ],
        [
            'id' => 'sets',
            'label' => 'Sets',
            'href' => '/sets',
        ],
        [
            'id' => 'blog',
            'label' => 'Nieuws',
            'href' => '/blog',
        ],
    ],

];
