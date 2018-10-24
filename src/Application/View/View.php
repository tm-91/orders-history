<?php
namespace Application\View;

class View
{
	protected $_params = null;
	protected $_controller = false;
	protected $_action = false;
	protected $_file = false;

	public function __construct($controller, $action, array $params = array()){
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_params = $params;
	}

	public function setParam($name, $value){
		$this->_params[$name] = $value;
	}

	public function unsetParam($name) {
		unset($this->_params[$name]);
	}

	public function isSetParam($name){
		return array_key_exists($name, $this->_params);
	}

	public function getParam($name){
		if ($this->isSetParam($name)) {
			return $this->_params[$name];
		}
	}

	public function render(){
        extract($this->_params);
        require __DIR__ . DIRECTORY_SEPARATOR . $this->_controller . DIRECTORY_SEPARATOR . $this->_action . '.php' ;
	}
}