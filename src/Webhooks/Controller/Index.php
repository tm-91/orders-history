<?php
namespace Webhooks\Controller;


class Index extends \Core\Controller\Controller
{
    public function indexAction(){
        $data = $this->_app->getResponseData();
        $order = false;
        try {
            $order = $this->_app->shop()->getOrder($data['order_id']);
        } catch (\Exception $ex) {
            // ignore exception
        }

        if ($order === false) {
            $this->_app->shop()->addOrder($data['order_id'], $data);
        } else {
            $changes = $order->geDiff($data);
            $order->insertHistory($changes);
            $order->updateCurrentData($data);
        }    
    }

    public function neworderAction(){
        $data = $this->_app->getResponseData();
        $this->_app->shop()->addOrder($data['order_id'], $data);
    }

    public function removeorderAction(){
        $data = $this->_app->getResponseData();
        $this->_app->shop()->removeOrderAndHistory($data['order_id']);
    }
}