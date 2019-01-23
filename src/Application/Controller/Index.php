<?php
namespace Application\Controller;

class Index extends \Core\Controller\Controller
{
    public function indexAction()
    {
        $orderId = $this->_app->getParam('id');
        $shop = $this->_app->shop();
        $order = false;
        try {
            $order = $shop->getOrder($orderId);
        } catch (\Exception $e) {
            // ignore
        }

        if ($order !== false) {
            $history = $order->getHistory();
        } else {
            $history = [];
        }

        $historyView = $this->_app->getView();
        $entryView = $this->_app->getView('Index/History/HistoryEntry');

        $historyView->render(['historyEntries' => $history, 'entryView' => $entryView]);
    }

}
