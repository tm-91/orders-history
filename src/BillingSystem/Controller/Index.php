<?php
namespace BillingSystem\Controller;


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
        $this->billing()->installShop($arguments);
        $this->_getShopOrders(\Core\Model\Shop::getInstance($arguments['shop']));
    }

    protected function _getShopOrders(\Core\Model\Shop $shop){
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
        $this->billing()->billingInstall($arguments);
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
    public function upgradeAction($arguments)
    {
        $this->billing()->upgrade($arguments);
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
    public function uninstallAction($arguments)
    {
        $shop = \Core\Model\Shop::getInstance($arguments['shop']);
        $shop->removeHistory();
        $this->billing()->uninstall($arguments);
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
        $this->billing()->billingSubscription($arguments);
    }

}