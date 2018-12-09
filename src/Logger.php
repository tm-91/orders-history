<?php

/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-09-28
 * Time: 18:26
 */
class Logger extends \DreamCommerce\ShopAppstoreLib\Logger
{
    public $_scope = '';
    protected $config = [];

    public function __construct($config)
    {
//        if(!defined("DREAMCOMMERCE_LOG_FILE")) {
//            throw new \Exception('Can not initialize logger. setup configs first');
//        }
        $this->config = $config;
    }

    public function setScope(array $scope) {
        $this->_scope = '[' . implode('][', $scope) . ']';
    }

    /**
     * @param string|array $scope
     */
    public function addScope($scope){
        if (is_array($scope)){
            $this->_scope = '[' . implode('][', $scope) . '] ';
        } else {
            $this->_scope .= '[' . $scope . ']';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = array()){
        parent::log($level, $this->_scope . ' ' . $message, $context);
    }

    public function test(){
        if ($this->config['debug']){
            echo $this->_scope . 'logger test' . PHP_EOL;
        } else {
            echo $this->_scope . 'debug mode is off' . PHP_EOL;
        }
    }
}