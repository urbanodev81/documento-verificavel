<?php

return [
    // false em sistema com banco por tenant: publique as migrations
    // (`vendor:publish --tag=documento-verificavel-migrations`) para database/migrations/tenant.
    'carregar_migrations' => env('DOCUMENTO_VERIFICAVEL_CARREGAR_MIGRATIONS', true),
];
