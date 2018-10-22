<?php

/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 20.09.18
 * Time: 15:21
 */
class Bootstraper_old
{
    protected static $globalConfigFile = false;

    protected static $config = [];

    public static function setEnvironmentsFromFile($filePath) {
        self::$globalConfigFile = $filePath;
        // todo dodać walidacje pliku
        self::$config = require self::$globalConfigFile;
        self::setEnvironments(self::getConfig());
    }

    public static function setEnvironments(array $config) {
        // various PHP configuration values
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

//        if (getenv('DREAMCOMMERCE_DEBUG')) {
//            $debug = true;
//        }
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
        self::updateConfigField($config);
    }

    public static function getConfig($name = null){
        if ($name === null) {
            return self::$config;
        }
        return self::$config[$name];
    }

    protected static function updateConfigField(array $config){
        self::$config = array_merge(self::$config, $config);
    }

}