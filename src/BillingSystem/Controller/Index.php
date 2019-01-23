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
        $mapResourceToWebhook = function ($resource, $map) {
            foreach ($map as $resourceField => $webhookField) {
                if (array_key_exists($resourceField, $resource)) {
                    if (isset($map[$resourceField])) {
                        $resource[$webhookField] = $resource[$resourceField];
                        unset($resource[$resourceField]);
                    } else {
                    }
                } else {
                }
            }
            return $resource;
        };

        $map = [
            'billing_address' => 'billingAddress',
            'shipping_tax_id' => 'shipping_vat',
            'shipping_tax_value' => 'shipping_vat_value',
            'shipping_tax_name' => 'shipping_vat_name',
            'delivery_address' => 'deliveryAddress',
        ];

//        $log = \Bootstraper::logger();
//        $log->_addScope(['_fetchAndAddShopOrders']);
//        $log->debug('going to fetch orders and add to db');
        $client = $shop->instantiateSDKClient();
//        $log->debug('created sdk client');
        $orderResource = new \DreamCommerce\ShopAppstoreLib\Resource\Order($client);
//        $log->debug('created dc resource order');
        $orderProductsResource = new \DreamCommerce\ShopAppstoreLib\Resource\OrderProduct($client);
//        $log->debug('created dc resource order products');
        $pageCount = 1;
        do {
            $page = $orderResource->limit(50)->page($pageCount)->get();
            foreach ($page as $order) {
//                $log->debug('got requested order with id: ' . $order['order_id']);
                $orderContent = json_decode(json_encode($order->getArrayCopy()), true);
//                $log->debug('resource order: ', $orderContent);
                 // 1. pobierz wszystkie produkty danego zamówienia
                // todo
                $productsPageCount = 1;
                $productsContent = [];
                do {
                    $productsPage = $orderProductsResource->limit(50)->filters(['order_id' => $order['order_id']])->get();
//                    $log->debug('amount of received order products: ' . count($productsPage));
                    // 2. rzutuj arrayObject do array
                    $productsContent = array_merge($productsContent, json_decode(json_encode($productsPage->getArrayCopy(), true)));
//                    $log->debug('resource order products: ', $productsContent);
                } while ($productsPageCount++ < $productsPage->getPageCount());
                 // 3. zmapuj tabele zamówienia wedłóg pól webhooka
                $orderContent = $mapResourceToWebhook($orderContent, $map);
                 // 4. dodaj pole produkty i przypisz do niego tabele z produktami zamówienia
                $orderContent['products'] = $productsContent;
                 // 5. dodaj zamówienie z przygotowanymi danymi
//                $log->debug('prepared order data: ', $orderContent);
                $id = $shop->addOrder($orderContent['order_id'], $orderContent);

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