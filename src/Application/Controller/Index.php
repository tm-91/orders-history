<?php
namespace Application\Controller;

use \Application\Model\Order;

class Index extends \Core\Controller\Controller
{
    public function indexAction()
    {
        $orderId = $this->_app->getParam('id');
        $shopId = $this->_app->shop()->getId();
        $order = Order::getInstance($shopId, $orderId);
        $history = $order->getHistory();
        $view = $this->_app->getView(['historyEntries' => $history]);
        $view->render();
    }
}
