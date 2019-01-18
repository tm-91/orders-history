<?php

class Bootstraper
{
    const DEFAULT_MODULE = 'Application';
    const MODULE_CLASS_NAME = 'App';
    protected static $_defaultConfigFile = __DIR__. '/Config.php';
    protected static $_config = false;
    protected static $_logger = false;

    /**
     * @return bool|\Logger
     */
    public static function logger(){
        return new \Logger(self::getConfig());
    }

    public static function processRequestUrl(){
        $path = trim($_GET['q'], '/');
        $path = str_replace('\\', '', $path);
        $pathElements = $path == '' ? array() : explode('/', $path);

        $module = false;
        switch (count($pathElements)) {
            case 0:
                /*
                 * /
                 */
                $module = self::DEFAULT_MODULE;
                break;
            case 2:
                /*
                * module/controller
                * controller/action
                */
                if (self::moduleExists(ucfirst($pathElements[0]))){
                    $module = ucfirst(array_shift($pathElements));
                } else {
                    $module = self::DEFAULT_MODULE;
                }
                break;
            default:
                /*
                 * module/controller/action
                 * module/
                 */
                $module = ucfirst(array_shift($pathElements));
                break;
        }
        $outcome = ['module' => $module, 'query' => $pathElements];
        self::logger()->debug('Requested module: ', $outcome);

        return $outcome;
    }

    public static function moduleExists($moduleName){
        return class_exists('\\' . $moduleName . '\\' . self::MODULE_CLASS_NAME);
    }

    public static function getModule($module){
        $path = '\\' . $module . '\\' . self::MODULE_CLASS_NAME;
        return new $path();
    }

    /**
     * @param bool $config
     * @return mixed
     * @throws Exception
     */
    public static function getConfig($config = false) {
        if ($config !== false) {
            if (isset(self::$_config[$config])) {
                return self::$_config[$config];
            }
            throw new \Exception('Configuration "' . $config . '" is not set');
        }
        return self::$_config;
    }

    public static function setConfig(array $config = null) {
        if (isset($config['timezone'])) {
            date_default_timezone_set($config['timezone']);
        }

        if (isset($config['php']['display_errors'])) {
            ini_set('display_errors', $config['php']['display_errors']);
        }

        // check debug mode options
        $debug = false;
        if (isset($config['debug'])) {
            if ($config['debug']) {
                $debug = true;
            }
        }

        define("DREAMCOMMERCE_DEBUG", $debug);

        // log errors
        $logFile = "php://stdout";
        if (isset($config['logFile'])) {
            if ($config['logFile']) {
                $logFile = $config['logFile'];
            } else {
                $config['logFile'] = false;
            }
        }
        define("DREAMCOMMERCE_LOG_FILE", $logFile);

        self::$_config = $config;
    }

    public static function bootstrap(){
        self::setConfig(require_once self::$_defaultConfigFile);
        self::$_logger = new \Logger(self::getConfig());
    }
}
