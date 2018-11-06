<?php
namespace Application\View;

class View
{
	protected $_params = null;
    protected $_viewDirectory = false;
	// protected $_controller = false;
	// protected $_action = false;
	// protected $_file = false;

	public function __construct($viewDirectory, array $params = array()){
		// $this->_controller = $controller;
		// $this->_action = $action;
		$this->_params = $params;
        $this->_viewDirectory = $viewDirectory;
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

	public function getParam($name = null){
		if ($name === null) {
		    return $this->_params;
        } else {
            if ($this->isSetParam($name)) {
                return $this->_params[$name];
            }
            // todo throw error
        }
	}

	public function render(){
        \Application\App::log('render ' . $this->_viewDirectory);
        extract($this->_params);
        // require __DIR__ . DIRECTORY_SEPARATOR . $this->_controller . DIRECTORY_SEPARATOR . $this->_action . '.php' ;
        require __DIR__ . DIRECTORY_SEPARATOR . $this->_viewDirectory . '.php';
	}

	public static function echoRec($array, array $translations = null) {
	    echo '<div style="">';
		foreach ($array as $key => $val) {
			if (is_array($val)) {
			    if ($translations && isset($translations[$key])){
			        $key = $translations[$key];
                }
                if (!is_numeric($key)){
                	echo '<div><h3>' . $key . '</h3></div>';
            	}
//				echo '--- ' . $key . ' ---</br>';
				static::echoRec($val, $translations);
				// echo '------';
			} else {
                if ($translations && isset($translations[$key])){
                    $key = $translations[$key];
                }
				echo '<div>' . $key . ' : ' . $val . '</div>';
				// echo $val;
			}
			// echo '</br>';
		}
		echo '</div>';
	}
}