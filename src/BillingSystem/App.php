<?php

namespace BillingSystem;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;
use DreamCommerce\ShopAppstoreLib\Logger;

class App
{

    /**
     * @var null|Handler
     */
    protected $handler = null;

    /*
     * @var array configuration placeholder
     */
    protected static $config = array();

    public static $logger;
    /*
     * @param string $entrypoint
     * @throws \Exception
     */
//    public function __construct($config)
//    {
//        // if (empty($_POST['shop_url']) || empty($_POST['action'])) {
//        //     die;
//        // }
//        // $entrypoint = $_POST['shop_url'];
//
//        self::$config = $config;
//        /*try {
//            // instantiate a handler
//            $handler = $this->handler = new Handler(
//                $entrypoint, $config['appId'], $config['appSecret'], $config['appstoreSecret']
//            );
//
//            // subscribe to particular events
//            $handler->subscribe('install', array($this, 'installHandler'));
//            $handler->subscribe('upgrade', array($this, 'upgradeHandler'));
//            $handler->subscribe('billing_install', array($this, 'billingInstallHandler'));
//            $handler->subscribe('billing_subscription', array($this, 'billingSubscriptionHandler'));
//            $handler->subscribe('uninstall', array($this, 'uninstallHandler'));
//        } catch (HandlerException $ex) {
//            throw new \Exception('Handler initialization failed', 0, $ex);
//        }*/
//    }

    public function bootstrap(){
        self::$config = \Bootstraper::getConfig();

file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap' . PHP_EOL, FILE_APPEND);

        if (empty($_POST['shop_url']) || empty($_POST['action'])) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - empty($_POST[shop_url]) || empty($_POST[action])' . PHP_EOL, FILE_APPEND);            
            die;
        }
        try {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try' . PHP_EOL, FILE_APPEND);            
            // instantiate a handler
            $handler = $this->handler = new Handler(
                $_POST['shop_url'], self::$config['appId'], self::$config['appSecret'], self::$config['appstoreSecret']
            );
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try: new handler created' . PHP_EOL, FILE_APPEND);
            // subscribe to particular events
            $controller = new \BillingSystem\Controller\Index();
            $handler->subscribe('install', array($controller, 'installAction'));
            $handler->subscribe('upgrade', array($controller, 'upgradeAction'));
            $handler->subscribe('billing_install', array($controller, 'billingInstallAction'));
            $handler->subscribe('billing_subscription', array($controller, 'billingSubscriptionAction'));
            $handler->subscribe('uninstall', array($controller, 'uninstallAction'));
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try: subscribe done' . PHP_EOL, FILE_APPEND);
        } catch (HandlerException $ex) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - catch: error' . PHP_EOL, FILE_APPEND);            
            throw new \Exception('Handler initialization failed', 0, $ex);
        }
    }

    /**
     * dispatches controller
     * @param array|null $data
     * @throws \Exception
     */
    public function dispatch($data = null)
    {

        try {
echo __METHOD__;
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch' . PHP_EOL, FILE_APPEND);
            $this->handler->dispatch($data);
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch - try this->handler->dispatch(data) done' . PHP_EOL, FILE_APPEND);            
        } catch (HandlerException $ex) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch - catch' . PHP_EOL, FILE_APPEND);            
            if ($ex->getCode() == HandlerException::HASH_FAILED) {
                throw new \Exception('Payload hash verification failed', 0, $ex);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function run($arguments){
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: run' . PHP_EOL, FILE_APPEND);
        $this->bootstrap();
        $this->dispatch();
    }

    public static function getConfig(){
        return self::$config;
    }

}
