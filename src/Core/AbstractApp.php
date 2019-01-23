<?php
namespace Core;

abstract class AbstractApp implements AppInterface
{
    /**
     * @var array configuration storage
     */
    protected static $config = false;

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'index';
    const MODULE_NAME = false;
    const CONTROLLER_NAMESPACE = 'Controller';
    const MODULE_NAMESPACE = 'Module';
    const VIEW_NAMESPACE = 'View';

    protected $_calledController = false;
    protected $_calledAction = false;

    /**
     * @var \Logger
     */
    protected $_logger = false;

    /**
     * @var bool|array
     */
    protected $_params = false;

    public function bootstrap(){
        $logger = new \Logger(static::getConfig());
        $logger->_setScope([static::MODULE_NAME]);
        $this->_logger = $logger;
    }

    public abstract function run(array $params = null);

    public static function getConfig($config = false){
        return \Bootstraper::getConfig($config);
    }

    /**
     * @return \Logger
     */
    public function logger() {
        return $this->_logger;
    }

    /**
     * dispatcher
     * @param array $urlElements
     * @throws \Exception
     */
    public function dispatch(array $urlElements = null)
    {
        if (is_null($urlElements)){
            throw new \Exception('invalid argument passed to dispatcher method');
        }
        $controller = false;
        $action = false;
        switch (count($urlElements)) {
            case 0:
                $controller = static::DEFAULT_CONTROLLER;
                $action = static::DEFAULT_ACTION;
                break;
            case 1:
                $controller = ucfirst(array_shift($urlElements));
                $action = static::DEFAULT_ACTION;
                break;
            default:
                $controller = ucfirst(array_shift($urlElements));
                $action = strtolower(array_shift($urlElements));
        }
        $this->callControllerAction($controller, $action, $urlElements);
    }

    public function callControllerAction($controllerName, $actionName, array $params = null) {
        $controller = '\\' . static::MODULE_NAME . '\\' . static::CONTROLLER_NAMESPACE . '\\' . $controllerName;
        $action = $actionName . 'Action';

        if (!class_exists($controller)) {
            throw new \Exception('Controller "' . $controller . '" not found');
        }

        if (!is_callable(array($controller, $action))) {
            throw new \Exception('Action "' . $actionName . '" not found');
        }
//
        $this->_calledController = $controllerName;
        $this->_calledAction = $actionName;

        $controller = new $controller($this);
        $success = call_user_func_array(array($controller, $action), $params);
        if ($success === false) {
            throw new \Exception('Failed to run controller "' . $controller . '" action "' . $action . '"');
        }
    }

    public function handleException(\Exception $exception) {
        $this->logger()->error(
            'Message: ' . $exception->getMessage()  . PHP_EOL .
            'Code: ' . $exception->getCode() . PHP_EOL .
            'Stack trace: ' . PHP_EOL . $exception->getTraceAsString()
        );
    }

    public function getResponseData($getRaw = false){
        $data = file_get_contents("php://input");
        if (!$getRaw) {
            $data = json_decode($data, true);
        }
        return $data;
    }

    public function getParam($param = null, $triggerException = true)
    {
        if ($this->_params === false) {
            throw new \Exception('App params array has not been initialized');
        }
        if ($param === null) {
            return $this->_params;
        }
        if (array_key_exists($param, $this->_params)) {
            return $this->_params[$param];
        } else {
            if ($triggerException) {
                throw new \Exception('Request parameter "' . $param . '" is not set');
            } else {
                return null;
            }
        }
    }
}