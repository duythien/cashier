<?php
/**
 *
 * Licensed under The GNU License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @link    http://phanbook.com Project
 * @since   1.0.0
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
use Phalcon\DI;
use Phalcon\Crypt;
use Phalcon\Security;
use Phalcon\Mvc\Router;
use Phalcon\Flash\Session;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Collection\Manager     as CollectionManager;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Frontend\None        as FrontendNone;
use Phalcon\Cache\Frontend\Output      as FrontendOutput;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Cache\Backend\Memcache;
use Phalcon\Cache\Backend\Memory       as MemoryBackend;
use Phalcon\Cache\Backend\File         as FileCache;
use Phalcon\Mvc\Url                    as UrlResolver;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Mvc\Model\Manager          as ModelsManager;
use Phalcon\Events\Manager             as EventsManager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Queue\Beanstalk;
use Phalcon\Config\Adapter\Php        as AdapterPhp;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\Model\Metadata\Redis as MetadataRedis;
use Phalcon\Security\Random;
use App\Mail\Mail;
use App\Auth\Auth;
use App\Acl\Acl;
use App\Utils\App as UtilsApp;
use App\Markdown\ParsedownExtra;
use App\Filesystem\FlysystemServiceProvider;



/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

// Create an event manager
$eventsManager = new EventsManager();

/**
 * Register the configuration itself as a service
 */
$config = include __DIR__ . '/config.php';
if (file_exists(__DIR__ . '/config.global.php')) {
    $overrideConfig = include __DIR__ . '/config.global.php';
    $config->merge($overrideConfig);
}

if (file_exists(__DIR__ . '/config.' . APPLICATION_ENV . '.php')) {
    $overrideConfig = include __DIR__ . '/config.' . APPLICATION_ENV . '.php';
    $config->merge($overrideConfig);
}
$di->set('config', $config, true);


/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set(
    'url',
    function () use ($di) {
        $url = new UrlResolver();
        $config = $di->get('config');
        $url->setBaseUri($config->application->baseUri);
        if (!$config->application->debug) {
            $url->setStaticBaseUri($config->application->production->staticBaseUri);
        } else {
            $url->setStaticBaseUri($config->application->development->staticBaseUri);
        }
        return $url;
    }
);

/**
 * Start the session the first time some component request the session service
 */
$di->set(
    'session',
    function () use ($di) {
        $sessionAdapter = $di->get('config')->application->session->adapter;
        $session        = new $sessionAdapter($di->get('config')->application->session->options->toArray());
        $session->start();

        return $session;
    },
    true
);

/**
 * This service controls the initialization of models, keeping record of relations
 * between the different models of the application.
 */
$di->setShared(
    'collectionManager',
    function () use ($eventsManager) {
        $collectionManager = new CollectionManager();
        $collectionManager->setEventsManager($eventsManager);

        return $collectionManager;
    }
);
$di->setShared(
    'modelsManager',
    function () use ($eventsManager) {
        $modelsManager = new ModelsManager();
        $modelsManager->setEventsManager($eventsManager);

        return $modelsManager;
    }
);

// Set the views cache service
$di->set(
    'viewCache',
    function () use ($di) {
        $config = $di->get('config');
        if ($config->application->debug) {
            return new MemoryBackend(new FrontendNone());
        } else {
            // Cache data for one day by default
            $frontCache = new FrontendOutput(['lifetime' => $config->cache->lifetime]);
            return new FileCache(
                $frontCache,
                [
                    'cacheDir' => $config->cache->cacheDir,
                    'prefix'   => $config->cache->prefix
                ]
            );
        }
    }
);


// Register the flash service with custom CSS classes
$di->set(
    'flashSession',
    function () {
        $flash = new Session(
            [
                'error'   => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice'  => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ]
        );

        return $flash;
    }
);

// Database connection is created based in the parameters defined in the configuration file
$di->set(
    'db',
    function () use ($di) {
        $connection = new Mysql(
            [
                'host'     => $di->get('config')->database->mysql->host,
                'username' => $di->get('config')->database->mysql->username,
                'password' => $di->get('config')->database->mysql->password,
                'dbname'   => $di->get('config')->database->mysql->dbname,
                //'schema'   => $di->get('config')->database->mysql->schema,
                // 'options'  => [
                //   //  \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $di->get('config')->database->mysql->charset
                // ]
            ]
        );
        $debug = $di->get('config')->application->debug;
        if ($debug) {
            $eventsManager = new EventsManager();
            $logger = new FileLogger(ROOT_DIR . '/logs/db.txt');
            //Listen all the database events
            $eventsManager->attach(
                'db',
                function ($event, $connection) use ($logger) {
                    /** @var Phalcon\Events\Event $event */
                    if ($event->getType() == 'beforeQuery') {
                        /** @var DatabaseConnection $connection */
                        $variables = $connection->getSQLVariables();
                        if ($variables) {
                            $logger->log($connection->getSQLStatement() . ' [' . join(',', $variables) . ']', \Phalcon\Logger::INFO);
                        } else {
                            $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                        }
                        //d($connection->getSQLStatement(), false) ;
                    }
                }
            );
            //Assign the eventsManager to the db adapter instance
            $connection->setEventsManager($eventsManager);
        }
        return $connection;
    },
    true // shared
);

$di->set(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    },
    true
);

$di->set(
    'crypt',
    function () use ($di) {
        $crypt = new Crypt();
        $crypt->setKey($di->get('config')->application->cryptSalt); //Use your own key!

        return $crypt;
    }
);

$di->set(
    'security',
    function () {

        $security = new Security();
        //Set the password hashing factor to 12 rounds
        $security->setWorkFactor(12);

        return $security;
    },
    true
);

//Set the models cache service
$di->set(
    'modelsCache',
    function () {
        // Cache data for one day by default
        $frontCache = new Data(['lifetime' => 86400]);

        // Memcached connection settings
        $cache = new Memcache(
            $frontCache,
            [
                'host' => 'localhost',
                'port' => 11211
            ]
        );

        return $cache;
    }
);

//Set mail swift
$di->set(
    'mail',
    function () {
        return new Mail();
    }
);
$di->set(
    'markdown',
    function () {
        return new ParsedownExtra();
    }
);
$di->set(
    'dispatcher',
    function () use ($di) {
        $eventsManager = new EventsManager;
        //$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);
        //$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);
        $dispatcher = new Dispatcher;
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }
);
$di->set(
    'auth',
    function () {
        return new Auth();
    }
);
$di->set(
    'acl',
    function () {
        return new Acl();
    }
);


//Translation application
$di->set(
    'translation',
    function () use ($di) {
        $language = $di->get('language');
        $path = ROOT_DIR . 'data/messages/' . $language . '.php';
        if (!file_exists($path)) {
            throw new \Exception("You must specify a language file for language '$language'");
        }
        $messages = require_once $path;
        //Return a translation object
        return new NativeArray([
            'content' => $messages
        ]);
    },
    true
);
$di->set(
    'language',
    function() use ($di, $config) {
        $cookies  = $di->get('cookies');
        $language = $config->defaultLang;
        if ($cookies->has('language')) {
            $language = $cookies->get('language');
        }
        return $language;
    },
    true
);
$di->set(
    'currency',
    function() use ($di, $config) {
        $cookies  = $di->get('cookies');
        $currency = $config->currency;
        if ($cookies->has('currency')) {
            $currency = unserialize($cookies->get('currency'));
        }
        return $currency;
    },
    true
);

$di->set(
    'filesystem',
    function () use ($di) {
        return new FlysystemServiceProvider($di);
    }
);
$di->set(
    'random',
    function ()  {
        return new Random();
    }
);
//Stores model meta-data in the Redis
// $di->set(
//     'modelsMetadata',
//     function () use ($di) {
//         $redis = $di->get('config')->redis;
//         $metaData = new MetadataRedis([
//             'host'      => $redis->host,
//             'port'      => $redis->port,
//             'prefix'    => $redis->prefix,
//             'lifetime'  => $redis->lifetime,
//             'persistent' => $redis->persistent
//         ]);

//         return $metaData;
//     },
//     true
// );
/**
 * Setting up volt
 */
$di->set(
    'volt',
    function ($view, $di) use ($config) {
        $volt = new Volt($view);
        $volt->setOptions(
            [
                'compiledPath'      => $config->application->view->compiledPath,
                'compiledSeparator' => $config->application->view->compiledSeparator,
                'compiledExtension' => $config->application->view->compiledExtension,
                'compileAlways'     => true,
            ]
        );
        $compiler = $volt->getCompiler();
        $compiler->addExtension(new \App\Tools\VoltFunctions());
        return $volt;
    },
    true
);

/**
 * The logger component
 */
$di->set(
    'logger',
    function () use ($di) {
        $logger = ROOT_DIR. 'logs/' . date('Y-m-d') . '.log';
        return new FileLogger($logger, ['model' => 'a+']);
    },
    true
);
$di->set(
    'app',
    function () {
        return new UtilsApp();
    },
    true
);
/**
 * Translation function call anywhere
 *
 * @param $string
 *
 * @return mixed
 */
if (!function_exists('t')) {
    function t($string)
    {
        $translation = DI::getDefault()->get('translation');
        return $translation->_($string);
    }
}
//Phalcon Debugger
if ($config->application->debug) {
    (new \Phalcon\Debug)->listen();
    if (!function_exists('d')) {
        function d($object, $kill = true)
        {
            echo '<pre style="text-aling:left">', print_r($object, true), '</pre>';
            $kill && exit(1);
        }
    }
}
//setup timezone
//date_default_timezone_set(!empty($di->get('auth')->getAuth()['timezone']) ? $di->get('auth')->getAuth()['timezone']  :'UTC');
