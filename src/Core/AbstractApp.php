<?php
namespace Core;

abstract class AbstractApp implements AppInterface
{
    /*
     * @var array configuration storage
     */
    protected static $config = false;
    public $dbHandler = false;
    /**
     * @var string module configuration file path
     */
//    const MODULE_CONFIG_FILE = false;
//    const CONFIG_FILE = \ModuleLoader::CONFIG_FILE;

//	protected $_defaultController = 'Index';
//    protected $_defaultAction = 'index';
//    protected $_controllerNamespace = __NAMESPACE__ . '\Controller';
//    protected $_viewNamespace = __NAMESPACE__ . '\View';
//    protected $_modelNamespace = __NAMESPACE__ . '\Model';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'index';
    const MODULE_NAME = false;
    const CONTROLLER_NAMESPACE = 'Controller';
    const MODULE_NAMESPACE = 'Module';
    const VIEW_NAMESPACE = 'View';

    // public function __construct(){
    //     $this->bootstrap();
    // }

//    protected static $_handler = false;
//    public function getDbHandler(){
//        if (self::$_handler === false) {
//            try {
//                $db = $this->getConfig('db');
//                self::$_handler = new \PDO($db['connection'], $db['user'], $db['pass']);
//            } catch (\PDOException $e){
//                if ($this->getConfig('debug')){
//                    throw $e;
//                } else {
//                    echo 'Database connection error';
//                }
//            }
//        }
//        return self::$_handler;

//    }

    public function bootstrap(){
        static::$config = static::getConfig();
    }
//    public abstract function dispatch();

    public abstract function run(array $params = null);

    public static function getConfig($config = false){
        return \Bootstraper::getConfig($config);
    }

    public static function log($message, $type = \Logger::TYPE_DEBUG){
        if (is_array($message)){
            \Logger::log('[module: ' . static::MODULE_NAME . '] ' . print_r($message, true), $type);
        } else {
            \Logger::log('[module: ' . static::MODULE_NAME . '] ' . $message, $type);
        }
    }

    /**
     * dispatcher
     * @param array $urlElements
     * @throws \Exception
     */
    public function dispatch(array $urlElements = null)
    {
        static::log('App: dispatch - start');
        static::log('App: dispatch url elements:');
//for ($i = 0; $i <= count($urlElements); $i++) {
//file_put_contents(DREAMCOMMERCE_LOG_FILE, $i . ' => ' . $urlElements[$i] . PHP_EOL, FILE_APPEND);
//}


        static::log($urlElements);

        if (is_null($urlElements)){
            static::log('invalid argument passed to dispatch method');
            throw new \Exception('invalid argument passed to dispatcher method');
            // todo
//            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//            $path = trim($path, '/');
//            $path = str_replace('\\', '', $path);
//            $pathElements = $path == '' ? array() : explode('/', $path);

        }

        $controller = false;
        $controllerName = false;
        $action = false;
        $actionName = false;
        switch (count($urlElements)) {
            case 0:
                $controllerName = static::DEFAULT_CONTROLLER;
                $actionName = static::DEFAULT_ACTION;
                break;
            case 1:
                $controllerName = ucfirst(array_shift($urlElements));
                $actionName = static::DEFAULT_ACTION;
                break;
            default:
                $controllerName = ucfirst(array_shift($urlElements));
                $actionName = strtolower(array_shift($urlElements));
        }
        // self::controllerActionExists($controller, $action, true);

//        $controller = $this->_controllerNamespace . '\\' . $controllerName;
        $controller = '\\' . static::MODULE_NAME . '\\' . static::CONTROLLER_NAMESPACE . '\\' . $controllerName;
        $action = $actionName . 'Action';

        if (!class_exists($controller)) {
            static::log('Controller name "' . $controller . '" not found');
            throw new \Exception('Controller "' . $controller . '" not found');
        }
        static::log('Controller: ' . $controllerName);

        if (!is_callable(array($controller, $action))) {
            static::log('Action "' . $actionName . '" not found');
            throw new \Exception('Action "' . $actionName . '" not found');
        }
        static::log('Action: ' . $actionName);

        $this->_calledController = $controllerName;
        $this->_calledAction = $actionName;

        $controller = new $controller($this);
        $success = call_user_func_array(array($controller, $action), $urlElements);
        if ($success === false) {
            static::log('Failed to run method "' . $action . '" of class "' . $controller . '"');
            throw new \Exception('Failed to run method "' . $action . '" of class "' . $controller . '"');
        }
        self::log('App: dispatch - end');

        static::log('koncówka dispatcha');
        static::log('$controllerName: ' . $controllerName);
        static::log('$actionName: ' . $actionName);
        
        $this->_calledController = $controllerName;
        $this->_calledAction = $actionName;

        static::log('$this->_calledController: ' . $this->_calledController);
        static::log('$this->_calledAction: ' . $this->_calledAction);
    }


    protected $_calledController = false;
    protected $_calledAction = false;

}