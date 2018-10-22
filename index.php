<?php
// region i18n
/*echo 'index';
if (empty($_GET['locale'])) {
    die();
}*/
chdir(__DIR__);

//endregion

//region php configuration
set_time_limit(0);
// endregion

try {
//    $config = require 'src/bootstrap.php';
    require 'src/bootstrap.php';

    // load module application class
   /* $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = ltrim($path, '/');
    $path = str_replace('\\', '', $path);
    $pathElements = explode('/', $path);
    $module = 'Application';
    if (count($pathElements) > 2){
        // $module = $pathElements[0];
        $module = array_shift($pathElements);
    }
    $application = $module . '\App';
    $app = new $application($config, $pathElements);*/
    //

    // $app = new Application\App($config);
    /*$app->bootstrap();
    $app->run();*/
    // require 'src/AppLoader.php';
// echo 'index2';
//    $configFile = __DIR__. '/src/Config.php';


    \Bootstraper::setConfig(\Bootstraper::loadConfig());
    $output = \Bootstraper::processRequestUrl();
    $app = \Bootstraper::getModule($output['module']);
    $app->run($output);



} catch (\Exception $ex) {
    @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

    // if ($app instanceof AbstractApp) {
        // $app->handleException($ex);
    // } else {
        if (class_exists("\\DreamCommerce\\ShopAppstoreLib\\Logger")) {
            $logger = new \DreamCommerce\ShopAppstoreLib\Logger;
            $logger->error('Message: ' . $ex->getMessage() . '; code: ' . $ex->getCode() . '; stack trace: ' . $ex->getTraceAsString());
        } else {
            die($ex->getMessage());
        }
    // }
}
