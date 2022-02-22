<?php
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
define('MAIN_DIR', __DIR__. DS. '..' .DS);
define('TRANSLATE_DIR', MAIN_DIR . 'translate'.DS);
define('UPLOADS_DIR', MAIN_DIR . '..' .DS. 'uploads');
defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS . '..' . DS. '..' . DS);

use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(ROOT . '.env')){
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT);
    $dotenv->load(true);
}
else{
    exit( ROOT.'.env not found' );
}

set_error_handler(function ($severity, $message, $file, $line) 
{
    if (error_reporting() & $severity) 
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});

$_ENV["API_IS_DEBUG"] = $_SERVER['REMOTE_ADDR'] == $_ENV["DEBUG_IP_ADDR"];

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/container.php');

$container = $containerBuilder->build();

$app = $container->get(App::class);

(require __DIR__ . '/routes.php')($app);

(require __DIR__ . '/middleware.php')($app);

return $app;
