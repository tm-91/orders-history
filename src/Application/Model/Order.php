<?php

namespace Application\Model;


use Application\App;

class Order
{
    protected $_shopId;
    protected $_orderId;

    public function __construct($shopId, $orderId)
    {
        // if (is_int($shopId)) {
            $this->_shopId = $shopId;
        // } else {
            // todo exception
        // }
        // if (is_int($orderId)) {
            $this->_orderId = $orderId;
        // } else {
            // todo exception
        // }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_orderId;
    }

    /**
     * @return mixed
     */
    public function getShopId(){
        return $this->_shopId;
    }

    /**
     * @return array|bool
     */
    public function getCurrentState(){
        $stm = \DbHandler::getDb()->prepare('SELECT `order_data` FROM orders_current_state WHERE shop_id=:shopId AND order_id=:orderId;');
        $stm->bindValue(':shopId', $this->getShopId(), \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $this->getId(), \PDO::PARAM_INT);
        if ($stm->execute()) {
            $data = $stm->fetch();
            return json_decode($data[0], true);
        }
        \Webhooks\App::log('FAILED TO EXECUTE getCurrentState()');
        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function pushCurrentState(array $data){
        $stm = \DbHandler::getDb()->prepare('INSERT INTO orders_current_state (shop_id, order_id, order_data) VALUES (:shopId, :orderId, :orderData) ON DUPLICATE KEY UPDATE order_data=VALUES(order_data)');
        $stm->bindValue(':shopId', $this->getShopId(), \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $this->getId(), \PDO::PARAM_INT);
        $stm->bindValue(':orderData', json_encode($data), \PDO::PARAM_STR);
        return $stm->execute();
    }

    // todo
    /**
     * @return array of objects type \Application\Model\Entity\OrderChange
     */
    public function getHistory(){
        // $db = \DbHandler::getDb();
        // $db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

        $si = $this->getShopId();
        $oi = $this->getId();
\Application\App::log('getHistory() shop id: ' . gettype($si) . '-' . $si);
\Application\App::log('getHistory() order id: ' . gettype($oi) . '-' . $oi);

        $stm = \DbHandler::getDb()->prepare('SELECT date, added, edited, removed FROM orders_history WHERE shop_id=:shopId AND order_id=:orderId ORDER BY date');
        $stm->bindValue(':shopId', $this->getShopId(), \PDO::PARAM_INT);
        $stm->bindValue(':orderId', $this->getId(), \PDO::PARAM_INT);

        if ($stm->execute()){
            $outcome = [];
            \Application\App::log('executed getHistory()');
            // $d = $stm->fetch();
            // var_dump($d);
            // \Application\App::log(print_r($d));
            while($row = $stm->fetch()) {
               \Application\App::log('----- getHistory fetched row ----');
    //            var_dump($row);
    //            \Application\App::log('---------------------------------');
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
            \Webhooks\App::log('getHistory error', 'error');
        }
    }


    public function pushHistory(\Application\Model\Entity\OrderChange $changes){
        $columns = [];
        $values = [];
        if ($added = $changes->getAddedData()) {
            $values[':added'] = json_encode($added);
            $columns[] = 'added';
        }
        if ($edited = $changes->getEditedData()) {
            $values[':edited'] = json_encode($edited);
            $columns[] = 'edited';
        }
        if ($removed = $changes->getRemovedData()) {
            $values[':removed'] = json_encode($removed);
            $columns[] = 'removed';
        }
        if (empty($values)) {
            return -1;
        }

        $stm = \DbHandler::getDb()->prepare(
            'INSERT INTO orders_history (shop_id, order_id, date, ' . implode(', ', $columns).
            ') VALUES (:shop_id, :order_id, :date, ' . implode(', ', array_keys($values)) . ');');
        $stm->bindValue(':shop_id', $changes->getShopId(), \PDO::PARAM_INT);
        $stm->bindValue(':order_id', $changes->getOrderId(), \PDO::PARAM_INT);
        $stm->bindValue(':date', $changes->getDate());
        foreach ($values as $key => $val) {
            $stm->bindValue($key, $val, \PDO::PARAM_STR);
        }
        try {    
            $stm->execute();    
        } catch (\PDOException $e) {
            \Webhooks\App::log("Error: " . $e->getMessage() . "\n");
        }
    }

    /**
     * @param array $data
     * @param null $time
     * @return Entity\OrderChange
     */
    public function geDiff(array $data, $time = null)
    {
        \Webhooks\App::log('----- in ge diff ----');
        $findEditedAndRemoved = function ($compareAgainst, $data) use (&$findEditedAndRemoved) {
            $outcome = [
                'e'=>[],
                'r'=>[]
            ];
            foreach ($compareAgainst as $key1 => $val1) {
                if (array_key_exists($key1, $data)) {
                    if (is_array($val1)) {
                        $out = $findEditedAndRemoved($val1, $data[$key1]);
                        if ($out['e']){
                            $outcome['e'][$key1] = $out['e'];
                        }
                        if ($out['r']){
                            $outcome['r'][$key1] = $out['r'];
                        }
                    } else {
                        if ($val1 !== $data[$key1]) {
                            $outcome['e'][$key1] = $data[$key1];
                        }
                    }
                } else {
                    $outcome['r'][$key1] = $val1;
                }
            }
            return $outcome;
        };

        $findAdded = function ($compareAgainst, $data) use (&$findAdded) {
            $outcome = [];
            foreach ($data as $key2 => $val2) {
                if (!key_exists($key2, $compareAgainst)) {
                    $outcome[$key2] = $val2;
                } elseif (is_array($val2)) {
                    if ($out = $findAdded($compareAgainst[$key2], $val2)) {
                        $outcome[$key2] = $out;
                    }
                }
            }
            return $outcome;
        };

        $currentOrder = $this->getCurrentState();
        \Webhooks\App::log('----- current state ----');
        if (is_array($currentOrder)){
            foreach($currentOrder as $key => $val) {
                \Webhooks\App::log($key);
            }       
        } else {
            \Webhooks\App::log($currentOrder);
        }
        \Webhooks\App::log('------------------------');

        if ($time === null) {
            $dt = new \DateTime();
            $dt->setTimestamp($_SERVER['REQUEST_TIME']);
            $time = $dt->format('Y-m-d H:i:s');
        }

        // \Webhooks\App::log('----- incoming data -----');
       // foreach($data as $key => $val) {
                // \Webhooks\App::log($key);
            // }
        // \Webhooks\App::log('-------------------------');

        $changes = new \Application\Model\Entity\OrderChange($this->getShopId(), $this->getId(), $time);
        if ($currentOrder == false) {
            \Webhooks\App::log('current state is false');
            $changes->setAddedData($data);
        } else {
            $extractedData = $findEditedAndRemoved($currentOrder, $data);
            $changes->setEditedData($extractedData['e']);
            $changes->setRemovedData($extractedData['r']);
            $changes->setAddedData($findAdded($currentOrder, $data));
        }
        return $changes;
    }
    
}