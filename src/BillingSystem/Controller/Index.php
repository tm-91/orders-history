<?php
namespace BillingSystem\Controller;

use \Core\Model\Shop;

class Index extends AbstractController
{
	/**
     * install action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - auth_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function installAction($arguments)
    {
//        $this->billing()->installShop($arguments);
//        $this->_getShopOrders(\Core\Model\Shop::getInstance($arguments['shop']));

        $shop = Shop::install(
            $arguments['shop'],
            $arguments['shop_url'],
            $arguments['application_version'],
            $arguments['client'],
            $arguments['auth_code']
        );
        $this->_fetchAndAddShopOrders($shop);
    }

    protected function _fetchAndAddShopOrders(\Core\Model\Shop $shop){
        $orderResource = new \DreamCommerce\ShopAppstoreLib\Resource\Order($shop->instantiateSDKClient());
        $pageCount = 1;
        do {
            $page = $orderResource->limit(50)->page($pageCount)->get();
            foreach ($page as $order) {
                $content = json_decode(json_encode($order->getArrayCopy()), true);
                $id = $shop->addOrder($order['order_id'], $content);
                \Bootstraper::logger()->debug('Added order id: ' . $order['order_id'] . ' (id in database table: ' . $id . ')');
            }
        } while ($pageCount++ < $page->getPageCount());
    }

    /**
     * client paid for the app
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function billingInstallAction($arguments)
    {
        $this->billing()->billingInstall($arguments['shop']);
    }

	/**
     * upgrade action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    /*public function upgradeAction($arguments)
    {
        $this->billing()->upgrade($arguments);
    }*/
    public function upgradeAction($arguments)
    {
        $shop = Shop::getInstance($arguments['shop']);
        $shop->upgrade($arguments);
    }

    /**
     * app is being uninstalled
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    /*public function uninstallAction($arguments)
    {
        $shop = \Core\Model\Shop::getInstance($arguments['shop']);
        $shop->removeOrdersAndHistory();
        $this->billing()->uninstall($arguments);
    }*/
    public function uninstallAction($arguments)
    {
        $shop = Shop::getInstance($arguments['shop']);
        $shop->uninstall();
    }

    /**
     * client paid for a subscription
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - subscription_end_time
     * - hash
     * - timestamp
     *
     * @param $arguments
     * @throws \Exception
     */
    public function billingSubscriptionAction($arguments)
    {
        $this->billing()->billingSubscription($arguments['shop'], $arguments['subscription_end_time']);
    }

}