<?php
namespace Webhooks\Controller;

use Application\Model\Order;

class Index extends \Core\Controller\Controller
{
    public function indexAction(){
        $data = $this->_app->getWebhookData();
        $order = Order::getInstance($this->_app->shop()->getId(), $data['order_id']);
        $changes = $order->geDiff($data);
        $order->insertHistory($changes);
        $order->updateCurrentData($data);
    }

    public function neworderAction(){
        $data = $this->_app->getWebhookData();
        Order::addNewOrder(
            $this->_app->shop()->getId(),
            $data['order_id'],
            $data
        );
    }

    public function removeorderAction(){
        $data = $this->_app->getWebhookData();
        $order = Order::getInstance($this->_app->shop()->getId(), $data['order_id']);
        $order->removeOrder();
    }
}