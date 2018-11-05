<?php
namespace Application\Controller;

use DreamCommerce\ShopAppstoreLib\Resource\Producer;

class Index extends ControllerAbstract
{
    public function indexAction()
    {
        $orderId = $this->app->getParam('id');
        $shopId = $this->app->shop()->getId();
        $order = new \Application\Model\Entity\Order($shopId, $orderId);
        $history = $order->getHistory();
        $view = $this->app->getView(['historyEntries' => $history, 'test' => 'dupa']);
        $view->render();
    }

    public function tutorialAction(){
        $client = $this->app->getClient();
        $producer = new Producer($client);
        $this['producers'] = $producer->get();
        $selected_ids = json_decode($_GET['id'], true);
        session_start();
        $_SESSION['stock_ids'] = $selected_ids;
    }

    public function changeAction(){
        session_start();
        $productsIds = $this->app->getClient()->productStock()->filters(array('stock_id' => array('in' => $_SESSION['stock_ids'])))->get();
        $selectedProducer = $this->app->escapreHtml($_POST['wybrany_producent']);
        foreach ($productsIds as $id){
            $this->app->getClient()->product->put($id, array($selectedProducer));
        }
    }

}
