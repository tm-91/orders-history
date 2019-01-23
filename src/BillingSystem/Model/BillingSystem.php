<?php

namespace BillingSystem\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

use \Core\Model\Tables\Billings as TableBillings;
use \Core\Model\Tables\Subscriptions as TableSubscriptions;
use \Core\Model\Shop;


class BillingSystem
{
    /**
     * @var bool|TableBillings
     */
    protected $_tableBillings = false;

    /**
     * @var bool|TableSubscriptions
     */
    protected $_tableSubscriptions = false;

    public function __construct(){
        $this->bootstrap();
    }

    protected function bootstrap(){
        $this->_tableSubscriptions = new TableBillings();
        $this->_tableBillings = new TableSubscriptions();
    }

    public function billingInstall($license){
        try {
            $shop = Shop::getInstance($license);
            // store payment event
            $this->_tableBillings->addBilling($shop->getId());
        } catch (\PDOException $ex) {
            throw new \Exception('Database error during billing install', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function billingSubscription($license, $subscriptionEndTime)
    {
        try {
            $shopId = Shop::getInstance($license)->getId();
            // make sure we convert timestamp correctly
            $expiresAt = date('Y-m-d H:i:s', strtotime($subscriptionEndTime));
            if (!$expiresAt) {
                throw new \Exception('Malformed timestamp');
            }
            // save subscription event
            $this->_tableSubscriptions->addSubscription($shopId, $expiresAt);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}