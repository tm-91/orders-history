<?php
namespace Application\Model;

class Order
{
	protected $_id;

    /**
     * @var \Application\Model\Tables\Order
     */
	protected $_tableOrders = false;

    /**
     * @var \Application\Model\Tables\OrderHistory
     */
	protected $_tableOrdersHistory = false;


	public function __construct($id){
		$this->_id = $id;
		$this->_bootstrap();
	}

	protected function _bootstrap(){
        $this->_tableOrders = new \Application\Model\Tables\Order();
        $this->_tableOrdersHistory = new \Application\Model\Tables\OrderHistory();
    }

	public static function addNewOrder($shopId, $orderId, array $currentState){
        $orderTable = new \Application\Model\Tables\Order();
        if ($orderTable->insertOrder($shopId, $orderId, $currentState)) {
            $id = \DbHandler::getDb()->lastInsertId();
            return $id;
        }
        return false;
    }

    public static function getInstance($shopId, $orderId) {
        $orderTable = new \Application\Model\Tables\Order();
        if ($id = $orderTable->getOrderId($shopId, $orderId)){
            return new self($id);
        }
        return false;
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

	public function insertHistory(\Application\Model\Helper\OrderHistoryEntry $historyEntry){
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
        $changes = new \Application\Model\Helper\OrderHistoryEntry($this->getId(), $time);
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
