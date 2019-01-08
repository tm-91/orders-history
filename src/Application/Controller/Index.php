<?php
namespace Application\Controller;

class Index extends \Core\Controller\Controller
{
    public function indexAction()
    {
        $orderId = $this->_app->getParam('id');
        $history = $this->_app->shop()
            ->getOrder($orderId)
            ->getHistory();
        $historyView = $this->_app->getView();
        $entryView = $this->_app->getView('Index/History/HistoryEntry');

        $historyView->render(['historyEntries' => $history, 'entryView' => $entryView]);
    }

}
