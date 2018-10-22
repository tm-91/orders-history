<?php
namespace Application\Controller;

use DreamCommerce\ShopAppstoreLib\Resource\Producer;

class Index extends ControllerAbstract
{
    public function indexAction()
    {
        // echo '<script src="https://cdn.dcsaas.net/js/dc-sdk-1.0.3.min.js"></script>';
        // echo '
        //     <script>
        //     (function () {
        //         "use strict";

        //         var styles;

        //         if (localStorage.getItem("styles")) {
        //             styles = JSON.parse(localStorage.getItem("styles"));
        //             injectStyles(styles);
        //         }

        //         window.shopAppInstance = new ShopApp(function (app) {
        //             app.init(null, function (params, app) {
        //                 if (localStorage.getItem("styles") === null) {
        //                     injectStyles(params.styles);
        //                 }
        //                 localStorage.setItem("styles", JSON.stringify(params.styles));

        //                 app.show(null, function () {
        //                     app.adjustIframeSize();
        //                 });
        //             }, function (errmsg, app) {
        //                 alert(errmsg);
        //             });
        //         }, true);

        //         function injectStyles (styles) {
        //             var i;
        //             var el;
        //             var sLength;

        //             sLength = styles.length;
        //             for (i = 0; i < sLength; ++i) {
        //                 el = document.createElement("link");
        //                 el.rel = "stylesheet";
        //                 el.type = "text/css";
        //                 el.href = styles[i];
        //                 document.getElementsByTagName("head")[0].appendChild(el);
        //             }
        //         }
        //     }());
        // </script>
        // ';
        // $echoArray = function ($data) use (&$echoArray) {
        //     foreach ($data as $key => $value){
        //         echo $key;
        //         echo ' => ';
        //         if (is_array($value)) {
        //             echo '</br>    ';
        //             $echoArray($value);
        //         } else {
        //             echo $value;
        //         }
        //     }
        // };

        $orderId = $this->app->getParam('id');

        // echo " order id :" . $orderId;

        $shopId = $this->app->shopData['id'];

        // echo " shop id: " . $shopId;
        // echo '</br>';
        
        $order = new \Application\Model\Order($shopId, $orderId);
        $history = $order->getHistory();
        $view = $this->app->getView(['historyEntries' => $history, 'test' => 'dupa']);
        $view->render();
        // foreach ($history as $historyEntry) {
        //     echo "</br></br>dodane:\n</br>";
        //     print_r($historyEntry->getAddedData());
        //     echo "</br></br>edytowane:\n</br>";
        //     print_r($historyEntry->getEditedData());
        //     echo "</br></br>usunięte:\n</br>";
        //     print_r($historyEntry->getRemovedData());
        // }
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
