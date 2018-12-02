<?php

namespace Application\Model;


class Order
{
    // todo
    public function getOrderId($shopId, $shopOrderId){
        $stm = \DbHandler::getDb()->prepare('SELECT `id` FROM `orders` WHERE `shop_id`=:shopId AND `shop_order_id`=:shopOrderId;');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':shopOrderId', $shopOrderId, \PDO::PARAM_INT);
        if ($stm->execute()) {
            return $stm->fetch(\PDO::FETCH_NUM)[0];
        }
        return false;
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function getCurrentData($id){
        $stm = \DbHandler::getDb()->prepare('SELECT `order_current_data` FROM `orders` WHERE `id`=:id;');
        $stm->bindValue(':id', $id, \PDO::PARAM_INT);
        if ($stm->execute()) {
            $data = $stm->fetch();
            return json_decode($data[0], true);
        }
        return false;
    }

    /**
     * @param $shopId
     * @param $shopOrderId
     * @param array $orderCurrentData
     * @return bool
     * @internal param array $data
     */
    // public function pushCurrentState($shopId, $orderId, array $data){
    public function insertOrder($shopId, $shopOrderId, array $orderCurrentData){
        $stm = \DbHandler::getDb()->prepare('INSERT INTO `orders` (`shop_id`, `shop_order_id`, `order_current_data`) VALUES (:shopId, :shopOrderId, :orderCurrentData);');
//        $stm = \DbHandler::getDb()->prepare('INSERT INTO `orders` (`shop_id`, `shop_order_id`, `order_current_data`) VALUES (:shopId, :orderId, :orderData) ON DUPLICATE KEY UPDATE order_current_data=VALUES(order_current_data)');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':shopOrderId', $shopOrderId, \PDO::PARAM_INT);
        $stm->bindValue(':orderCurrentData', json_encode($orderCurrentData), \PDO::PARAM_STR);
        return $stm->execute();
    }


    public function updateCurrentData($id, array $orderCurrentData){
        $stm = \DbHandler::getDb()->prepare('UPDATE TABLE `orders` SET `order_current_data`=:orderCurrentData WHERE `id`=:id');
        $stm->bindValue(':id', $id, \PDO::PARAM_INT);
        $stm->bindValue(':orderCurrentData', json_encode($orderCurrentData), \PDO::PARAM_STR);
        return $stm->execute();
    }

    // todo
    /*public function removeOrder($id){
        $stm  =\DbHandler::getDb()->prepare('')
    }*/



//    const ID = 'id';
//    const SHOP_ID = 'shop_id';
//    const SHOP_ORDER_ID =  'shop_order_id';

//    const ORDER_CURRENT_DATA = 'order_current_data';
//    public function updateCurrentData($id, array $values){
//        $set = [];
////        $valuesToSet = [];
//        foreach ($values as $column => $val){
//            $set[] = $column . '=:' . $column;
//        }
//        $stm = \DbHandler::getDb()->prepare('UPDATE TABLE `orders` SET (`shop_id`, `shop_order_id`, `order_current_data`) WHERE `id`=:id');
//        $stm->bindValue(':id', $id, \PDO::PARAM_INT);
//
//        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
//        $stm->bindValue(':orderData', json_encode($data), \PDO::PARAM_STR);
//        return $stm->execute();
//    }

    /**
     * @return array|bool of objects type \Application\Model\Entity\OrderChange
     */
    public function getHistory($shopId, $orderId){
        $stm = \DbHandler::getDb()->prepare('SELECT date, added, edited, removed FROM orders_history WHERE shop_id=:shopId AND order_id=:orderId ORDER BY date');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);

        if ($stm->execute()){
             $outcome = [];
             while($row = $stm->fetch()) {
                 $historyEntry = new \Application\Model\Entity\OrderChange($row['shop_id'], $row['order_id'], $row['date']);
                 if (isset($row['added'])){
                     $historyEntry->setAddedData(json_decode($row['added'], true));
                 }
                 if (isset($row['edited'])){
                     $historyEntry->setEditedData(json_decode($row['edited'], true));
                 }
                 if (isset($row['removed'])){
                     $historyEntry->setRemovedData(json_decode($row['removed'], true));
                 }
                 $outcome[] = $historyEntry;
             }
            return $outcome;
        } else {
            return false;
        }
    }


    public function pushHistory($shopId, $orderId, $date, array $added = null, array $edited = null, array $removed = null){
        $columns = [];
        $values = [];
        if ($added) {
            $values[':added'] = json_encode($added);
            $columns[] = 'added';
        }
        if ($edited) {
            $values[':edited'] = json_encode($edited);
            $columns[] = 'edited';
        }
        if ($removed) {
            $values[':removed'] = json_encode($removed);
            $columns[] = 'removed';
        }
        if (empty($values)) {
            return -1;
        }

        $stm = \DbHandler::getDb()->prepare(
            'INSERT INTO orders_history (shop_id, order_id, date, ' . implode(', ', $columns).
            ') VALUES (:shop_id, :order_id, :date, ' . implode(', ', array_keys($values)) . ');');
        $stm->bindValue(':shop_id', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':order_id', $orderId, \PDO::PARAM_INT);
        $stm->bindValue(':date', $date);
        foreach ($values as $key => $val) {
            $stm->bindValue($key, $val, \PDO::PARAM_STR);
        }
        try {    
            $stm->execute();    
        } catch (\PDOException $e) {
            \Webhooks\App::log("Error: " . $e->getMessage() . "\n");
        }
    }
    
    public function removeCurrentState($shopId, $orderId){
        $stm = \DbHandler::getDb()->prepare('DELETE FROM `orders_current_state` WHERE `shop_id`=:shopId AND `order_id`=:orderId;');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
        return $stm->execute();
    }

    public function removeHistory($shopId, $orderId){
        $stm = \DbHandler::getDb()->prepare('DELETE FROM `orders_history` WHERE `shop_id`=:shopId AND `order_id`=:orderId;');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
        return $stm->execute();
    }
}