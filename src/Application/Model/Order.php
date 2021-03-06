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

    /**
     * @var bool|\Logger
     */
	protected $logger = false;

	public function __construct($id){
		$this->_id = $id;
		$this->_bootstrap();
	}

	protected function _bootstrap(){
        $this->_tableOrders = new TableOrder();
        $this->_tableOrdersHistory = new TableOrderHistory();
        $this->logger = \Bootstraper::logger();
        $this->logger->_setScope(['Model', 'Order', 'id: ' . $this->getId()]);
    }

	public static function addNewOrder($shopId, $orderId, array $currentState){
        $orderTable = new TableOrder();
        $orderTable->insertOrder($shopId, $orderId, $currentState);
        $id = \DbHandler::getDb()->lastInsertId();
        \Bootstraper::logger()->debug("Created new order id $id (order id in shop: $orderId) in shop id $shopId; order data:\n", $currentState);
        return $id;
    }

    public static function getInstance($shopId, $orderId) {
        $orderTable = new TableOrder();
        if ($id = $orderTable->getOrderId($shopId, $orderId)){
            \Bootstraper::logger()->debug('get order id ' . $id . ' (order id in shop: ' . $orderId . ') from shop id ' . $shopId);
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
        $data = $this->_tableOrders->getCurrentData($this->getId());
        $this->logger->debug('get current state data', $data);
        return $data;
    }

    /**
     * @param array $data
     */
    public function updateCurrentData(array $data) {
        $this->_tableOrders->updateCurrentData($this->getId(), $data);
        $this->logger->debug('updated current state data; current data:', $data);
    }

    /**
     * @return array|bool array of \Application\Model\Helper\OrderHistoryEntry objects
     */
    public function getHistory(){
		$history = $this->_tableOrdersHistory->getHistory($this->getId());
		$this->logger->debug('get history entries. Amount: ' . count($history));
		foreach ($history as $entry) {
            $this->logger->debug("Entry:");
            $this->logger->debug('date: ' . $entry->getDate('Y-m-d H:i:s'));
            $this->logger->debug('added data: ', $entry->getAddedData());
            $this->logger->debug('edited data: ', $entry->getEditedData());
            $this->logger->debug('removed data: ', $entry->getRemovedData());
        }
		return $history;
	}

	public function insertHistory(HistoryEntry $historyEntry){
        $this->_tableOrdersHistory->insertHistory(
            $this->getId(),
            $historyEntry->getDate(),
            $historyEntry->getAddedData(),
            $historyEntry->getEditedData(),
            $historyEntry->getRemovedData()
        );
        $this->logger->debug("Added order history entry");
        $this->logger->debug('date: ' . $historyEntry->getDate('Y-m-d H:i:s'));
        $this->logger->debug('added data: ', $historyEntry->getAddedData());
        $this->logger->debug('edited data: ', $historyEntry->getEditedData());
        $this->logger->debug('removed data: ', $historyEntry->getRemovedData());
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
                                    $outcome[$type][$key] = $delta;
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
                                            $outcome[$type][$key][$baseSubKey] = $delta;
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
        $currentOrder = $this->getCurrentData();
        if ($time === null) {
            $time = new \DateTime();
            $time->setTimestamp($_SERVER['REQUEST_TIME']);
        }
        $this->logger->debug("comparing current order data with: ", $data);
        $delta = $findDiff($currentOrder, $data, $includeAdditionalFields);
        $entry = new \Application\Model\Helper\OrderHistoryEntry($this->getId(), $time);
        $entry->setRemovedData($delta['R']);
        $entry->setAddedData($delta['A']);
        $entry->setEditedData($delta['E']);
        return $entry;
    }

    public function removeOrder(){
        $id = $this->getId();
        $this->_tableOrders->removeOrder($id);
        $this->logger->debug('Removed order from database');
    }

    public function clearHistory(){
        $this->_tableOrdersHistory->removeOrderHistory($this->getId());
        $this->logger->debug('Removed order history entries');
    }

}
