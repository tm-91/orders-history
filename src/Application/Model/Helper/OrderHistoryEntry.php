<?php
namespace Application\Model\Helper;

class OrderHistoryEntry
{
	protected $_addedData = [];
	protected $_editedData = [];
	protected $_removedData = [];

    /**
    * @var \DateTime
    */
	protected $_date;

	protected $_orderId;
	protected $_id;

    public function __construct($orderId, \DateTime $date){
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
     * @param null $format
     * @return mixed
     */
    public function getDate($format = null){
        if ($format) {
            return $this->_date->format($format);
        }
        return $this->_date;
    }

    /**
     * @return mixed
     */
    public function getOrderId(){
        return $this->_orderId;
    }
}