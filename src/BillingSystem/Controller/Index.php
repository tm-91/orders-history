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
        $id = $this->billing()->installShop($arguments);
        \Bootstraper::logger()->debug('shop installed');
//        \Bootstraper::logger()->debug('shop id: ', $id);
//        $shop = new \Core\Model\Shop($id);
        $shop = \Core\Model\Shop::getInstance($arguments['shop']);
        \Bootstraper::logger()->debug('got shop instance');
        $this->_getShopOrders($shop);
    }

    protected function _getShopOrders(\Core\Model\Shop $shop){
        $log = \Bootstraper::logger();
        $log->_setScope(['billing system', 'controller', 'index']);
        $log->debug('I get shop orders');
        $orderResource = new \DreamCommerce\ShopAppstoreLib\Resource\Order($shop->instantiateSDKClient());
//        $page = $orderResource->limit(50)->get();
//        $log->debug('I got first page');
//        $log->debug('received items: ' . $page->getCount());
//        $ordersList = $page->getArrayCopy();
////        $log->debug('ordersList:', $ordersList);
//        // todo
//        $pages = $page->getPageCount();
//        $log->debug('number of data pages: ' . $pages);
//        if ($page->getPage() < $pages) {
//            for ($i = 2; $i <= $pages; $i++) {
//                $log->debug('I am going to get page: ' . $i);
//                $page = $orderResource->limit(50)->page($i)->get();
//                $ordersList = array_merge($ordersList, $page->getArrayCopy());
//                $log->debug('I got page and merged incoming data array with previous one');
//            }
//        }
//        $log->debug('I fetched all data. I am going to add orders to shop\'s (with id: ' . $shop->getId() . ') database');
//        foreach ($ordersList as $order) {
//            $id = $shop->addOrder($order['order_id'], $order);
//            $log->debug('Added order id: ' . $order['order_id'] . ' (id in database table: ' . $id . ')');
//        }
        $pageCount = 1;
        do {
            $page = $orderResource->limit(50)->page($pageCount)->get();
            $log->debug('received: ' . count($page) . ' orders');
            $log->debug('current page: ' . $pageCount);
            $log->debug('pages amount: ' . $page->getPageCount());
            foreach ($page as $order) {
                $log->debug('I am going to add new order id: ' . $order['order_id']);
                $log->debug('shop id:' . $shop->getId());
                $log->debug('order id: ' . $order['order_id']);

                $content = json_decode(json_encode($order->getArrayCopy()), true);
//                $log->debug('order content: ', $content);
                $id = $shop->addOrder($order['order_id'], $content);
//                $id = \Application\Model\Order::addNewOrder($shop->getId(), $order['order_id'], $order->getArrayCopy());
                $log->debug('Added order id: ' . $order['order_id'] . ' (id in database table: ' . $id . ')');
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