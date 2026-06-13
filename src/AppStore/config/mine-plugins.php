<?php

declare(strict_types=1);

return [
    'paths' => [
        'plugins' => base_path('plugins'),
        'state' => storage_path('app/mine-plugins/state.json'),
        'cache' => base_path('bootstrap/cache/mine_plugins.php'),
    ],

    'runtime' => [
        'throw_on_failure' => false,
    ],
];
