<?php
namespace Webhooks\Controller;

use Application\Model\Order;

class Index extends \Core\Controller\Controller
{
    public function indexAction(){
        $data = $this->_app->getResponseData();
        $log = $this->_app->logger();
        $log->_addScope(['controller','indexAction']);
        $log->debug('response data:', $data);
        $order = false;
        try {
//            $order = Order::getInstance($this->_app->shop()->getId(), $data['order_id']);
            $order = $this->_app->shop()->getOrder($data['order_id']);
        } catch (\Exception $ex) {
            // ignore exception
        }

        if ($order === false) {
//            Order::addNewOrder($this->_app->shop()->getId(), $data['order_id'], $data);
            $this->_app->shop()->addOrder($data['order_id'], $data);
        } else {
            $changes = $order->geDiff($data);
            $order->insertHistory($changes);
            $order->updateCurrentData($data);
        }    
    }

    public function neworderAction(){
        $data = $this->_app->getResponseData();
        $log = $this->_app->logger();
        $log->_addScope(['controller','newOrderAction']);
        $log->debug('new order response data:', $data);
//        Order::addNewOrder(
//            $this->_app->shop()->getId(),
//            $data['order_id'],
//            $data
//        );
        $this->_app->shop()->addOrder($data['order_id'], $data);
    }

    public function removeorderAction(){
        $data = $this->_app->getResponseData();
//        $order = Order::getInstance($this->_app->shop()->getId(), $data['order_id']);
//        $order->removeOrder();
        $this->_app->shop()->removeOrderAndHistory($data['order_id']);
    }
}