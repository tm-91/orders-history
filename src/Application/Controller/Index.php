<?php
namespace Application\Controller;

class Index extends \Core\Controller\Controller
{
    public function indexAction()
    {
        $orderId = $this->_app->getParam('id');
        $shopId = $this->_app->shop()->getId();
        $order = new \Application\Model\Entity\Order($shopId, $orderId);
        $history = $order->getHistory();
        $view = $this->_app->getView(['historyEntries' => $history]);
        $view->render();
    }
}
