<?php
namespace Application\Model;

use \Application\Model\Tables\Order as TableOrder;
use \Application\Model\Tables\OrderHistory as TableOrderHistory;
use \Application\Model\Helper\OrderHistoryEntry as HistoryEntry;

class Order
{
	protected $_id;

    /**
     * @var TableOrder
     */
	protected $_tableOrders = false;

    /**
     * @var TableOrderHistory
     */
	protected $_tableOrdersHistory = false;


	public function __construct($id){
		$this->_id = $id;
		$this->_bootstrap();
	}

	protected function _bootstrap(){
        $this->_tableOrders = new TableOrder();
        $this->_tableOrdersHistory = new TableOrderHistory();
    }

	public static function addNewOrder($shopId, $orderId, array $currentState){
        $orderTable = new TableOrder();
        $orderTable->insertOrder($shopId, $orderId, $currentState);
        return \DbHandler::getDb()->lastInsertId();
    }

    public static function getInstance($shopId, $orderId) {
        $orderTable = new TableOrder();
        if ($id = $orderTable->getOrderId($shopId, $orderId)){
            return new self($id);
        }
        throw new \Exception('Did not found order id: ' . $orderId . ' in shop id: ' . $shopId);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return array|bool
     */
    public function getCurrentData() {
        return $this->_tableOrders->getCurrentData($this->getId());
    }

    /**
     * @param array $data
     */
    public function updateCurrentData(array $data) {
        $this->_tableOrders->updateCurrentData($this->getId(), $data);
    }

    /**
     * @return array|bool array of \Application\Model\Entity\OrderChange objects
     */
    public function getHistory(){
		return $this->_tableOrdersHistory->getHistory($this->getId());
	}

	public function insertHistory(HistoryEntry $historyEntry){
        $this->_tableOrdersHistory->insertHistory(
            $this->getId(),
            $historyEntry->getDate(),
            $historyEntry->getAddedData(),
            $historyEntry->getEditedData(),
            $historyEntry->getRemovedData()
        );
    }

	/**
     * @param array $data
     * @param null $time
     * @return \Application\Model\Helper\OrderHistoryEntry
     */
    public function geDiff(array $data, $time = null)
    {
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

        $currentOrder = $this->getCurrentData();
        if ($time === null) {
            $time = new \DateTime();
            $time->setTimestamp($_SERVER['REQUEST_TIME']);
        }
        $changes = new HistoryEntry($this->getId(), $time);
        if ($currentOrder == false) {
            $changes->setAddedData($data);
        } else {
            $extractedData = $findEditedAndRemoved($currentOrder, $data);
            $changes->setEditedData($extractedData['e']);
            $changes->setRemovedData($extractedData['r']);
            $changes->setAddedData($findAdded($currentOrder, $data));
        }
        return $changes;
    }

    public function removeOrder(){
        $this->_tableOrders->removeOrder($this->getId());
    }

    public function clearHistory(){
        $this->_tableOrdersHistory->removeOrderHistory($this->getId());
    }

}
