<?php
namespace Webhooks\Controller;

class Index extends \Core\Controller\Controller
{
    /*public function indexAction(){
        $orders = new \Application\Model\Orders();
        $data = $this->_app->getWebhookData();
        $order = $orders->getOrderCurrentState($data['order_id']);
        $changes = $orders->getOrdersDiff($order, $data);
        $orders->pushHistoryEntry($changes);
    }*/

    public function indexAction(){
        $data = $this->_app->getWebhookData();
        \Webhooks\App::log('SHOP ID: ' . $this->_app->shopId . ';');
        $order = new \Application\Model\Order($this->_app->shopId, $data['order_id']);
        $changes = $order->geDiff($data);
        \Webhooks\App::log('----- pushing history -----');
        $order->pushHistory($changes);
        $order->pushCurrentState($data);
    }

	public function indexActionXXX(){
		$logger = new \DreamCommerce\ShopAppstoreLib\Logger;

		file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [Webhook]: dziala! indexAction' . PHP_EOL, FILE_APPEND);
		$data = file_get_contents('php://input');

		$logger->debug('data type: ' . gettype($data));

		file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [Webhook]: Index: indexAction - data:' . PHP_EOL, FILE_APPEND);
		// file_put_contents(DREAMCOMMERCE_LOG_FILE, $data . PHP_EOL, FILE_APPEND);

	

		$data2 = json_decode($data, true);
		\Webhooks\App::log(print_r($data2, true));

		$logger->debug('json_decode(data) data type: ' . gettype($data2));

		// $orderId = $data2['order_id'];
		$logger->debug('data json decoded order_id: ' . $data2['order_id']);
		$logger->debug($data2);
		// file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [Webhook]: Index: indexAction - data[order_id] = "' . $orderId . '"' . PHP_EOL, FILE_APPEND);

	

		file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [Webhook]: Index: indexAction - var_export:' . PHP_EOL, FILE_APPEND);
		$var = var_export($data);
		file_put_contents(DREAMCOMMERCE_LOG_FILE, $var . PHP_EOL, FILE_APPEND);
//		echo 'WEBHOOK - DZIAŁAM!!';
		// $logger = new \DreamCommerce\ShopAppstoreLib\Logger;
		// $logger->debug('Webhook response body (decoded): ' . var_export($data, true));
		file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . 'data count: ' . count($var) . PHP_EOL, FILE_APPEND);
	}
}