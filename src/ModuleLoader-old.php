<?php

class ModuleLoader{

	protected $_config = array();
	protected $_moduleName;
	protected $_defaultModuleName;
	protected $_appClassName;
	protected $_app;
	protected $_requestUrlElements = array();


//    const CONFIG_FILE = __DIR__. '/src/Config.php';

	public function __construct($defaultModuleName = 'Application', $moduleAppClassName = "App"){
//		$this->_config = $config;
		// todo przenieść do config
		$this->_defaultModuleName = $defaultModuleName;
		$this->_appClassName = $moduleAppClassName;
	}

	protected function findModuleInUrl(){
		$path = trim($_GET['q'], '/');
		$path = str_replace('\\', '', $path);
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [ModuleLoader]: findModuleInUrl: path:' . $path . PHP_EOL, FILE_APPEND);		
		$pathElements = $path == '' ? array() : explode('/', $path);
		switch (count($pathElements)) {
			case 0:
				$module = $this->getDefaultModuleName();
				break;
			case 2:
				if ($this->checkIfModuleExists(ucfirst($pathElements[0]))){
					$module = ucfirst(array_shift($pathElements));
				} else {
					$module = $this->getDefaultModuleName();
				}
				break;
			default:
				$module = ucfirst(array_shift($pathElements));
				break;
		}
		$this->_moduleName = $module;
		$this->_requestUrlElements = $pathElements;			
		return $this;
	}

	public function checkIfModuleExists($module){
		return class_exists('\\' . $module . '\\' . $this->getAppClassName());
	}

	public function getAppByModuleName($moduleName){
		$appClass = $this->getAppClassName();
		$namespace = '\\' . $moduleName . '\\' . $appClass;
		if (!$this->checkIfModuleExists($moduleName)){
			throw new Exception('Module "' . $moduleName . '" main class "' . $appClass . '" not found');
		}		
		return new $namespace();
	}

	public function loadModuleApp(){
		$this->findModuleInUrl();
		$module = $this->getModuleName();
//		$config = $this->getConfig();
		$this->_app = $this->getAppByModuleName($module);

		return $this;
	}

	public function getRequestUrlElements(){
		return $this->_requestUrlElements;
	}

	public function getModuleName(){
		return $this->_moduleName;
	}
	
	public function getApp(){
		return $this->_app;
	}

	public function getAppClassName(){
		return $this->_appClassName;
	}

	public function getDefaultModuleName(){
		return $this->_defaultModuleName;
	}

//	public function getConfig(){
//		return $this->_config;
//	}
//
//	public function setConfig(array $config){
//		$this->_config = $config;
//	}

	/*public function loadModule(){
		$this->parseUrl();

		$module = $this->getModuleClass();
		$class = $this->getAppClassName();
		$config = $this->getConfig();
		$namespace = $module . '\\' . $class;
		// todo sprawdzenie czy dana klasa istnieje
		$this->_app = new $namespace($config);
		$this->_app->setUrlArray($this->getUrlElements());
		return $this;
	}*/

	/*public function dispatch(){
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = ltrim($path, '/');
        $path = str_replace('\\', '', $path);
        $urlElements = explode('/', $path);
        $this->_urlElements = $urlElements;

        $module = $this->_defaultModule;
        if (count($urlElements) > 2){
            $module = array_shift($urlElements[0]);
        }
        $this->_module = $module;

        // $app = new $module . \App();
//        $this->pathElements = $pathElements;
//        $application = $module . '\App';


        $controller = ucfirst(array_shift($urlElements));
        $action = strtolower(array_shift($urlElements)) . 'Action';

        $class = '\\' . $module . '\\Controller\\' . $controller;

        if(!class_exists($class)){
            throw new Exception('Controller name "' . $controller . '" in module "' . $module . '" not found');
        }

        $params = $_GET;
        if(!empty($params['id'])){
            $params['id'] = @json_decode($params['id']);
        }

        $actionName = strtolower($queryData[1]).'Action';
        $controller = new $class($this, $params);
        if(!method_exists($controller, $actionName)){
            throw new Exception('Action not found');
        }

        $controller['shopUrl'] = $this->shopData['url'];

        $result = call_user_func_array(array($controller, $actionName), array_slice($queryData, 2));

        if($result!==false) {
            $viewName = strtolower($queryData[0]) . '/' . strtolower($queryData[1]);
            $controller->render($viewName);
        }
    }*/

}