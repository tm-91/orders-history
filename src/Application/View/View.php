<?php
namespace Application\View;

class View
{
	protected $_params = [];
    protected $_viewDirectory = false;

    /**
     * @var bool|\Psr\Log\LoggerInterface
     */
    protected $_logger = false;

	public function __construct($viewDirectory, \Logger $logger){
//		$this->_params = $params;
        $this->_viewDirectory = $viewDirectory;
        $logger->_addScope('View');
        $this->_logger = $logger;
	}

	public function setParams(array $params){
		$this->_params = array_merge($this->_params, $params);
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

	/*public function render(){
        $this->_logger->debug('rendering ' . $this->_viewDirectory);
        extract($this->_params);
        require __DIR__ . DIRECTORY_SEPARATOR . $this->_viewDirectory . '.php';
	}*/

    public function render(array $params = null){
        $this->_logger->debug('rendering ' . $this->_viewDirectory);
        if ($params) {
            $this->setParams($params);
        }
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $this->_viewDirectory . '.php';
        if (file_exists($filePath)) {
            extract($this->_params);
            require __DIR__ . DIRECTORY_SEPARATOR . $this->_viewDirectory . '.php';
        } else {
            throw new \Exception('Did not found view file: ' . $filePath);
        }
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

	public function logger(){
		return $this->_logger;
	}
}