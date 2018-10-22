<?php
namespace Application\Model\Entity;

class OrderChange
{
	protected $_addedData = [];
	protected $_editedData = [];
	protected $_removedData = [];
	protected $_date;
	protected $_shopId;
	protected $_orderId;
	protected $_id;
	const ADDED_DATA_KEY = '1';
	const EDITED_DATA_KEY = '0';
    const REMOVED_DATA_KEY = '-1';

    public function __construct($shopId, $orderId, $date){
        $this->_shopId = $shopId;
        $this->_orderId = $orderId;
        $this->_date = $date;
    }

    /**
     * @param array $data
     */
    public function setAddedData(array $data){
        $this->_addedData = $data;
    }

    /**
     * @param array $data
     */
    public function addAddedData(array $data){
        $this->_addedData = array_merge($this->_addedData, $data);
    }

    /**
     * @param array $data
     */
    public function setEditedData(array $data){
        $this->_editedData = $data;
    }

    /**
     * @param array $data
     */
    public function addEditedData(array $data) {
        $this->_editedData = array_merge($this->_editedData, $data);
    }

    /**
     * @param array $data
     */
    public function setRemovedData(array $data){
        $this->_removedData = $data;
    }

    /**
     * @param array $data
     */
    public function addRemovedData(array $data) {
        $this->_removedData = array_merge($this->_removedData, $data);
    }

    /**
     * @return array|null
     */
    public function getAddedData(){
        return $this->_addedData;
    }

    /**
     * @return array|null
     */
    public function getEditedData(){
        return $this->_editedData;
    }

    /**
     * @return array|null
     */
    public function getRemovedData(){
        return $this->_removedData;
    }

    /**
     * @return mixed
     */
    public function getDate(){
        return $this->_date;
    }

    /**
     * @return mixed
     */
    public function getOrderId(){
        return $this->_orderId;
    }

    /**
     * @return mixed
     */
    public function getShopId(){
        return $this->_shopId;
    }
}