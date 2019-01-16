<?php

namespace Application\Model\Tables;


class Order
{
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
     * @throws \Exception
     */
    public function insertOrder($shopId, $shopOrderId, array $orderCurrentData){
        $stm = \DbHandler::getDb()->prepare('INSERT INTO `orders` (`shop_id`, `shop_order_id`, `order_current_data`) VALUES (:shopId, :shopOrderId, :orderCurrentData) ON DUPLICATE KEY UPDATE order_current_data=VALUES(order_current_data)');
        $stm->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stm->bindValue(':shopOrderId', $shopOrderId, \PDO::PARAM_INT);
        $stm->bindValue(':orderCurrentData', json_encode($orderCurrentData), \PDO::PARAM_STR);
        if ($stm->execute() === false){
            \Bootstraper::logger()->error('insert order failed; order data: ', $orderCurrentData);
            throw new \Exception('Failed to add order id: ' . $shopOrderId . ' to shop id: ' . $shopId);
        }
    }


    public function updateCurrentData($id, array $orderCurrentData){
        $stm = \DbHandler::getDb()->prepare('UPDATE `orders` SET `order_current_data`=:orderCurrentData WHERE `id`=:id;');
        $stm->bindValue(':id', $id, \PDO::PARAM_INT);
        $stm->bindValue(':orderCurrentData', json_encode($orderCurrentData), \PDO::PARAM_STR);
        if ($stm->execute() === false){
            \Bootstraper::logger()->error('update order failed; order data: ', $orderCurrentData);
            throw new \Exception('Failed to update order id: ' . $id);
        }
    }

    public function removeOrder($id){
        $stm = \DbHandler::getDb()->prepare('DELETE FROM `orders` WHERE `id`=:id;');
        $stm->bindValue(':id', $id, \PDO::PARAM_INT);
        if ($stm->execute() === false){
            throw new \Exception('Failed to remove order id: ' . $id);
        }
    }

    public function removeShopOrders($shopId) {
        $stmt = \DbHandler::getDb()->prepare('DELETE FROM `orders` WHERE `shop_id`=:shopId;');
        $stmt->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        if ($stmt->execute() === false) {
            throw new \Exception('Failed to remove all shop id ' . $shopId . ' orders');
        }
        return true;
    }
}