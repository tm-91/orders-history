<?php

namespace Application\Model;


class Order
{

    /**
     * @return array|bool
     */
    public function getCurrentState($shopId, $orderId){
        $stm = \DbHandler::getDb()->prepare('SELECT `order_data` FROM orders_current_state WHERE shop_id=:shopId AND order_id=:orderId;');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
        if ($stm->execute()) {
            $data = $stm->fetch();
            return json_decode($data[0], true);
        }
        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function pushCurrentState($shopId, $orderId, array $data){
        $stm = \DbHandler::getDb()->prepare('INSERT INTO orders_current_state (shop_id, order_id, order_data) VALUES (:shopId, :orderId, :orderData) ON DUPLICATE KEY UPDATE order_data=VALUES(order_data)');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
        $stm->bindValue(':orderData', json_encode($data), \PDO::PARAM_STR);
        return $stm->execute();
    }

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

//            $rows = $stm->fetchAll();

//            if ($rows) {
//                if (is_array($rows)) {
//                    \Application\App::log(print_r($rows));
//                } else {
//                    \Application\App::log($rows);
//                }
//            } else {
//                \Application\App::log('model order getHistory; outcome is empty!', 'error');
//            }

//            $outcome = [];
//            foreach ($rows as $row){
//                if (isset($row['added'])){
//                    $row['added'] = json_decode($row['added'], true);
//                }
//                if (isset($row['edited'])){
//                    $row['edited'] = json_decode($row['edited'], true);
//                }
//                if (isset($row['removed'])){
//                    $row['removed'] = json_decode($row['removed'], true);
//                }
//            }

            return $outcome;
        } else {
            \Webhooks\App::log('getHistory error', 'error');
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