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
            static::logger()->error('invalid argument passed to dispatch method');
            throw new \Exception('invalid argument passed to dispatcher method');
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
        $controller = '\\' . static::MODULE_NAME . '\\' . static::CONTROLLER_NAMESPACE . '\\' . $controllerName;
        $action = $actionName . 'Action';

        if (!class_exists($controller)) {
            static::logger()->error('Controller name "' . $controller . '" not found');
            throw new \Exception('Controller "' . $controller . '" not found');
        }

        if (!is_callable(array($controller, $action))) {
            static::logger()->error('Action "' . $actionName . '" not found');
            throw new \Exception('Action "' . $actionName . '" not found');
        }

        $this->_calledController = $controllerName;
        $this->_calledAction = $actionName;

        $controller = new $controller($this);
        $success = call_user_func_array(array($controller, $action), $urlElements);
        if ($success === false) {
            static::logger()->error('Failed to run method "' . $action . '" of class "' . $controller . '"');
            throw new \Exception('Failed to run method "' . $action . '" of class "' . $controller . '"');
        }
        
        $this->_calledController = $controllerName;
        $this->_calledAction = $actionName;
    }
}