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
        $id = \DbHandler::getDb()->lastInsertId();
        \Bootstraper::logger()->debug('Created new order id ' . $id . ' (shop id ' . $orderId . ') in shop id ' . $shopId);
        return $id;
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
     * @param bool $includeAdditionalFields
     * @return HistoryEntry
     */
    public function geDiff(array $data, $time = null, $includeAdditionalFields = false)
    {
        $isAssoc = function (array $arr)
        {
            return array_keys($arr) !== range(0, count($arr) - 1);
        };

        $findDiff = function ($base, $compare, $includeAddedFields = false) use ($isAssoc, &$findDiff) {
            $outcome = [
                'R' => [],
                'A' => [],
                'E' => []
            ];
            foreach ($base as $key => $value) {
                if (array_key_exists($key, $compare)) {
                    if (is_array($value)) {
                        $valueCompare = $compare[$key];
                        if ($isAssoc($valueCompare) || $isAssoc($value)) {
                            $d = $findDiff($value, $valueCompare);
                            foreach ($d as $type => $delta) {
                                if ($delta) {
                                    $outcome[$type] = [$key => $delta];
                                }
                            }
                        } else {
                            if ($status = (count($valueCompare) - count($value))) {
                                if ($status < 0) {
                                    $baseSubArray = $value;
                                    $compareSubArray = $valueCompare;
                                    $type = 'R';
                                } else {
                                    $baseSubArray = $valueCompare;
                                    $compareSubArray = $value;
                                    $type = 'A';
                                }
                                foreach ($baseSubArray as $baseSubKey => $baseSubVal) {
                                    $exists = false;
                                    foreach ($compareSubArray as $valueSubArray) {
                                        $d2 = $findDiff($baseSubVal, $valueSubArray);
                                        if (empty($d2['R']) && empty($d2['A']) && empty($d2['E'])) {
                                            $exists = true;
                                            break;
                                        }
                                    }
                                    if ($exists == false) {
                                        $outcome[$type][$key][$baseSubKey] = $baseSubVal;
                                    }
                                }
                            } else {
                                foreach ($value as $baseSubKey => $baseSubVal) {
                                    $d3 = $findDiff($baseSubVal, $valueCompare[$baseSubKey]);
                                    foreach ($d3 as $type => $delta) {
                                        if ($delta) {
                                            $outcome[$type] = [$key => [$baseSubKey => $delta]];
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        if ($value !== $compare[$key]) {
                            $outcome['E'][$key] = $compare[$key];
                        }
                    }
                } else {
//                    echo "key \"$key\" does not exists in comparing array\n";
                    if ($includeAddedFields) {
                        $outcome['R'][$key] = $value;
                    }
                }
            }
            return $outcome;
        };

        /*$findRemovedArray = function (array $base, array $compare) {
            $outcome = [];
            foreach ($base as $arbKey => $arrBase) {
                $removed = true;
                foreach ($compare as $arr) {
                    if ($arrBase == $arr) {
                        $removed = false;
                        break;
                    }
                }
                if ($removed) {
                    $outcome[] = $arrBase;
                }
            }
            return $outcome;
        };

        $findDiff = function ($base, $compare, $includeAddedFields = false) use ($isAssoc, $findRemovedArray, &$findDiff) {
            $outcome = [
                'R' => [],
                'A' => [],
                'E' => []
            ];
            foreach ($base as $key => $value) {
                if (array_key_exists($key, $compare)) {
                    if (is_array($value)) {
                        $valueCompare = $compare[$key];
                        if ($isAssoc($valueCompare)) {
                            $d1 = $findDiff($value, $valueCompare);
                            if ($d1['R']) {
                                $outcome['R'] = [$key => $d1['R']];
                            }
                            if ($d1['A']) {
                                $outcome['A'] = [$key => $d1['A']];
                            }
                            if ($d1['E']) {
                                $outcome['E'] = [$key => $d1['E']];
                            }
                        } else {
                            $status = count($valueCompare) - count($value);
                            if ($status < 0) {
                                if ($r = $findRemovedArray($value, $valueCompare)) {
                                    $outcome['R'][$key] = $r;
                                }
                            } elseif ($status > 0) {
                                if ($a = $findRemovedArray($valueCompare, $value)) {
                                    $outcome['A'][$key] = $a;
                                }
                            } else {
                                foreach ($value as $baseSubKey => $baseSubVal) {
                                    $d2 = $findDiff($baseSubVal, $valueCompare[$baseSubKey]);
                                    if ($d2['R']) {
                                        $outcome['R'] = [$key => [$baseSubKey => $d2['R']]];
                                    }
                                    if ($d2['A']) {
                                        $outcome['A'] = [$key => [$baseSubKey => $d2['A']]];
                                    }
                                    if ($d2['E']) {
                                        $outcome['E'] = [$key => [$baseSubKey => $d2['E']]];
                                    }
                                }
                            }
                        }
                    } else {
                        if ($value !== $compare[$key]) {
                            $outcome['E'][$key] = $compare[$key];
                        }
                    }
                } else {
                    if ($includeAddedFields) {
                        $outcome['R'][$key] = $value;
                    }
                }
            }
            return $outcome;
        };*/

        $currentOrder = $this->getCurrentData();
        if ($time === null) {
            $time = new \DateTime();
            $time->setTimestamp($_SERVER['REQUEST_TIME']);
        }

        $delta = $findDiff($currentOrder, $data, $includeAdditionalFields);
        $entry = new \Application\Model\Helper\OrderHistoryEntry($this->getId(), $time);
        $entry->setRemovedData($delta['R']);
        $entry->setAddedData($delta['A']);
        $entry->setEditedData($delta['E']);
        return $entry;
    }

    public function removeOrder(){
        $this->_tableOrders->removeOrder($this->getId());
    }

    public function clearHistory(){
        $this->_tableOrdersHistory->removeOrderHistory($this->getId());
    }

}
