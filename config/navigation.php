<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront Navigation Menu
    |--------------------------------------------------------------------------
    |
    | The navigation tree shared with the front-end via Inertia. Each item is
    | an array shape: ['id' => string, 'label' => string, 'href' => ?string,
    | 'children' => ?array]. This is a placeholder until the menu is managed
    | through the CMS.
    |
    */

    'menu' => [
        [
            'id' => 'parts',
            'label' => 'Onderdelen',
            'children' => [
                [
                    'id' => 'parts-bricks',
                    'label' => 'Bricks',
                    'children' => [
                        ['id' => 'parts-bricks-1x', 'label' => '1 x N Bricks', 'href' => '/c/bricks/1x'],
                        ['id' => 'parts-bricks-2x', 'label' => '2 x N Bricks', 'href' => '/c/bricks/2x'],
                        ['id' => 'parts-bricks-round', 'label' => 'Round Bricks', 'href' => '/c/bricks/round'],
                    ],
                ],
                [
                    'id' => 'parts-plates',
                    'label' => 'Plates',
                    'children' => [
                        ['id' => 'parts-plates-1x', 'label' => '1 x N Plates', 'href' => '/c/plates/1x'],
                        ['id' => 'parts-plates-2x', 'label' => '2 x N Plates', 'href' => '/c/plates/2x'],
                    ],
                ],
                ['id' => 'parts-tiles', 'label' => 'Tiles', 'href' => '/c/tiles'],
                ['id' => 'parts-technic', 'label' => 'Technic', 'href' => '/c/technic'],
            ],
        ],
        [
            'id' => 'minifigs',
            'label' => 'Minifiguren',
            'children' => [
                ['id' => 'minifigs-star-wars', 'label' => 'Star Wars', 'href' => '/c/minifigs/star-wars'],
                ['id' => 'minifigs-city', 'label' => 'City', 'href' => '/c/minifigs/city'],
                ['id' => 'minifigs-castle', 'label' => 'Castle', 'href' => '/c/minifigs/castle', 'children' => [
                    ['id' => 'minifigs-star-wars', 'label' => 'Star Wars', 'href' => '/c/minifigs/star-wars'],
                    ['id' => 'minifigs-city', 'label' => 'City', 'href' => '/c/minifigs/city'],
                    ['id' => 'minifigs-castle', 'label' => 'Castle', 'href' => '/c/minifigs/castle', 'children' => [
                        ['id' => 'minifigs-star-wars', 'label' => 'Star Wars', 'href' => '/c/minifigs/star-wars'],
                        ['id' => 'minifigs-city', 'label' => 'City', 'href' => '/c/minifigs/city'],
                        ['id' => 'minifigs-castle', 'label' => 'Castle', 'href' => '/c/minifigs/castle', 'children' => [
                            ['id' => 'minifigs-star-wars', 'label' => 'Star Wars', 'href' => '/c/minifigs/star-wars'],
                            ['id' => 'minifigs-city', 'label' => 'City', 'href' => '/c/minifigs/city'],
                            ['id' => 'minifigs-castle', 'label' => 'Castle', 'href' => '/c/minifigs/castle'],
                        ], ],
                    ], ],
                ], ],
            ],
        ],
        [
            'id' => 'sets',
            'label' => 'Sets',
            'children' => [
                ['id' => 'sets-new', 'label' => 'Nieuw', 'href' => '/c/sets/new'],
                ['id' => 'sets-retired', 'label' => 'Retired', 'href' => '/c/sets/retired'],
            ],
        ],
        ['id' => 'deals', 'label' => 'Aanbiedingen', 'href' => '/c/deals'],
    ],

];
