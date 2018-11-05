<?php
namespace Application\Model\Entity;

class Order
{
	protected $_shopId;
	protected $_id;
	protected $_orderModel = false;

	public function __construct($shopId, $orderId){
		$this->_shopId = $shopId;
		$this->_id = $orderId;
		$this->_orderModel = new \Application\Model\Order();
	}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return mixed
     */
    public function getShopId() {
        return $this->_shopId;
    }

    /**
     * @return array|bool
     */
    public function getCurrentState() {
        return $this->_orderModel->getCurrentState($this->getShopId(), $this->getId());
    }

    /**
     * @param array $data
     */
    public function pushCurrentState(array $data) {
        $this->_orderModel->pushCurrentState($this->getShopId(), $this->getId(), $data);
    }

    /**
     * @return array|bool array of \Application\Model\Entity\OrderChange objects
     */
    public function getHistory(){
		return $this->_orderModel->getHistory($this->getShopId(), $this->getId());
	}

	public function pushHistory(\Application\Model\Entity\OrderChange $historyEntry){
        $this->_orderModel->pushHistory(
            $this->getShopId(),
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
     * @return \Application\Model\Entity\OrderChange
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

        $currentOrder = $this->getCurrentState();
        if ($time === null) {
            $dt = new \DateTime();
            $dt->setTimestamp($_SERVER['REQUEST_TIME']);
            $time = $dt->format('Y-m-d H:i:s');
        }
        $changes = new \Application\Model\Entity\OrderChange($this->getShopId(), $this->getId(), $time);
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

    public function removeCurrentState(){
        $this->_orderModel->removeCurrentState($this->getShopId(), $this->getId());
    }

    public function removeAllHistory(){
        $this->_orderModel->removeHistory($this->getShopId(), $this->getId());
    }

}
