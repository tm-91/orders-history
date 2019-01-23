<?php

namespace BillingSystem;

use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;

class App extends \Core\AbstractApp
{

    /**
     * @var null|Handler
     */
    protected $_handler = null;

    const MODULE_NAME = 'BillingSystem';

    public function bootstrap(){
        parent::bootstrap();

        $this->_params = $_POST;

        $this->validateRequest();

        try {
            // instantiate a handler
            $handler = $this->_handler = new Handler(
                $this->getParam('shop_url'), self::getConfig('appId'), self::getConfig('appSecret'), self::getConfig('appstoreSecret')
            );
            // subscribe to particular events
            $controller = new \BillingSystem\Controller\Index($this);
            $handler->subscribe('install', array($controller, 'installAction'));
            $handler->subscribe('upgrade', array($controller, 'upgradeAction'));
            $handler->subscribe('billing_install', array($controller, 'billingInstallAction'));
            $handler->subscribe('billing_subscription', array($controller, 'billingSubscriptionAction'));
            $handler->subscribe('uninstall', array($controller, 'uninstallAction'));
        } catch (HandlerException $ex) {
            throw new \Exception('Handler initialization failed', 0, $ex);
        }
    }

    /**
     * dispatches controller
     * @param array|null $data
     * @throws \Exception
     */
    public function dispatch(array $data = null)
    {
        try {
            $this->_handler->dispatch($data);
        } catch (HandlerException $ex) {
            if ($ex->getCode() == HandlerException::HASH_FAILED) {
                throw new \Exception('Payload hash verification failed', 0, $ex);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function run(array $arguments = null){
        $this->bootstrap();
        $this->dispatch();
    }

    public function validateRequest(){
        if ($this->getParam('shop_url', false) == null){
            $this->logger()->error('Params: ', $this->getParam());
            throw new \Exception('Invalid request. Parameter "shop_url" has not been set');
        }
        if ($this->getParam('action', false) == null){
            $this->logger()->error('Params: ', $this->getParam());
            throw new \Exception('Invalid request. Parameter "action" has not been set');
        }
    }
}
