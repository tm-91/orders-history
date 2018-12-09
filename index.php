<?php
// region i18n
chdir(__DIR__);

//endregion

//region php configuration
set_time_limit(0);
// endregion

try {
    require 'src/bootstrap.php';
//    \Bootstraper::setConfig(\Bootstraper::loadConfigFile());
    \Bootstraper::setupConfigs();
    $output = \Bootstraper::processRequestUrl();
    $app = \Bootstraper::getModule($output['module']);
    $app->run($output);



} catch (\Exception $ex) {
    // todo error handling
    /*
     * if (method_exists($app, 'handleError'))
     * {
     *      $app->handleError($ex);
     * }
     */
    @header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

        if (class_exists("\\DreamCommerce\\ShopAppstoreLib\\Logger")) {
            $logger = new \DreamCommerce\ShopAppstoreLib\Logger;
            $logger->error('Message: ' . $ex->getMessage() . '; code: ' . $ex->getCode() . '; stack trace: ' . $ex->getTraceAsString());
        } else {
            die($ex->getMessage());
        }
}
