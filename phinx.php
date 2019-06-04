<?php
declare(strict_types=1);

if (getenv('DB_NAME') === false) {
    Dotenv\Dotenv::create(__DIR__)->load();
}

return [
    'paths'        => [
        'migrations' => "%%PHINX_CONFIG_DIR%%/src/Infra/DbMigrations",
    ],
    'environments' => [
        'default_database' => 'default',
        'default'          => [
            'adapter' => "mysql",
            'host'    => getenv('DB_HOST'),
            'name'    => getenv('DB_NAME'),
            'user'    => getenv('DB_USER'),
            'pass'    => getenv('DB_PASSWORD'),
        ],
    ],
];
