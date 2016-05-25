<?php
use Phalcon\Mvc\Application;

error_reporting(E_ALL);
ini_set('memory_limit', '-1');


if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__) .'/');
}
if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));
}

require_once ROOT_DIR . 'vendor/autoload.php';

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Phalcon\Cashier'           => ROOT_DIR . 'src',
        'App\Models'                => ROOT_DIR . 'tests/models'
    ]
);

$loader->register();

include  ROOT_DIR . 'tests/service.phalcon.php';

return new Application($di);
