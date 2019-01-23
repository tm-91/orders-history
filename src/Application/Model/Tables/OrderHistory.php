<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-02
 * Time: 16:38
 */

namespace Application\Model\Tables;


class OrderHistory
{
    /**
     * @param $orderId
     * @return array|bool of objects type \Application\Model\Entity\OrderChange
     */
    public function getHistory($orderId){
        $stm = \DbHandler::getDb()->prepare('SELECT date, added, edited, removed FROM orders_history WHERE order_id=:orderId ORDER BY date');
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);

        if ($stm->execute()){
            $outcome = [];
            while($row = $stm->fetch()) {
                $historyEntry = new \Application\Model\Helper\OrderHistoryEntry($orderId, new \DateTime($row['date']));
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

    /**
     * @param $orderId
     * @param \DateTime $date
     * @param array|null $added
     * @param array|null $edited
     * @param array|null $removed
     * @return int
     * @throws \Exception
     */
    public function insertHistory($orderId, \DateTime $date, array $added = null, array $edited = null, array $removed = null){
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
            'INSERT INTO orders_history (order_id, date, ' . implode(', ', $columns).
            ') VALUES (:order_id, :date, ' . implode(', ', array_keys($values)) . ');');
        $stm->bindValue(':order_id', $orderId, \PDO::PARAM_INT);
        $stm->bindValue(':date', $date->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        foreach ($values as $key => $val) {
            $stm->bindValue($key, $val, \PDO::PARAM_STR);
        }
        if ($stm->execute() === false) {
            throw new \Exception('Failed to add history entry to order id: ' . $orderId);
        }
    }

    /**
     * @param $orderId
     * @throws \Exception
     */
    public function removeOrderHistory($orderId){
        $stm = \DbHandler::getDb()->prepare('DELETE FROM `orders_history` WHERE `order_id`=:orderId;');
        $stm->bindValue(':orderId', $orderId, \PDO::PARAM_INT);
        if ($stm->execute() === false) {
            throw new \Exception('Failed to remove history entry of order id: ' . $orderId);
        }
    }
}