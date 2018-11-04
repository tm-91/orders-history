<?php
namespace Webhooks\Controller;

class Index extends \Core\Controller\Controller
{
    public function indexAction(){
        $data = $this->_app->getWebhookData();
        $order = new \Application\Model\Order($this->_app->shopId, $data['order_id']);
        $changes = $order->geDiff($data);
        $order->pushHistory($changes);
        $order->pushCurrentState($data);
    }

    public function neworderAction(){
    	$data = $this->_app->getWebhookData();
    	$order = new \Application\Model\Order($this->_app->shopId, $data['order_id']);
    	$order->pushCurrentState($data);
    }

    public function removeorderAction(){
        $data = $this->_app->getWebhookData();
        $order = new \Application\Model\Order($this->_app->shopId, $data['order_id']);
        $order->removeHistory();
        $order->removeCurrentState();
    }
}