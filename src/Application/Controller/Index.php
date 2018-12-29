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
        $historyView = $this->_app->getView();
        $entryView = $this->_app->getView('Index/History/HistoryEntry');

        $historyView->render(['historyEntries' => $history, 'entryView' => $entryView]);
    }

}
