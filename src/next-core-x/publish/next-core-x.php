<?php

use Mine\NextCoreX\Default\Client;
use Mine\NextCoreX\Default\LocalStore;

return [
    'contracts' =>  [
        'clientContract'    =>  Client::class,
        'localStoreContract'    => LocalStore::class,
    ]
];