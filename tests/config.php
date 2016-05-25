<?php
return new \Phalcon\Config(
    [
        /**
         * The name of the database, username,password for Phanbook
         */
        'database'  => [
            'mysql'     => [
                'host'     => 'localhost',
                'username' => 'root',
                'password' => '',
                'dbname'   => 'phanbook',
                'charset'  => 'utf8',
            ]
        ],
        'stripe' => [
            'model' => 'App\Models\Users',
            'secretKey' => null,
            'publishKey' => null
        ],
        /**
         * Application settings
         */
        'application' => [
            /**
             * The site name, you should change it to your name website
             */
            'name'                => 'Phanbook',
            /**
             * In a few words, explain what this site is about.
             */
            'tagline'             => 'A Q&A, Discussion PHP platform',
            'publicUrl'           => 'http://phanbook.com',
            /**
             * Change URL cdn if you want it
             */
            'development'    => [
                'staticBaseUri' => '/',
            ],
            'production'  => [
                'staticBaseUri' => '/',
            ],
            /**
             * For developers: Phanbook debugging mode.
             *
             * Change this to true to enable the display of notices during development.
             * It is strongly recommended that plugin and theme developers use
             * in their development environments.
             */
            'debug'               => true
        ],
    ]
);
