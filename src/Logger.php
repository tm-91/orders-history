<?php

/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-09-28
 * Time: 18:26
 */
class Logger extends \DreamCommerce\ShopAppstoreLib\Logger
{
    protected $_scope = [];
    protected $_scopeString = '';
    protected $config = [];

    public function __construct(array $config)
    {
//        if(!defined("DREAMCOMMERCE_LOG_FILE")) {
//            throw new \Exception('Can not initialize logger. setup configs first');
//        }
        $this->config = $config;
    }

    protected function _toScopeString(array $scope){
        if ($scope) {
            return '[' . implode('][', $scope) . ']';
        }
        return '';
    }

    public function _setScope(array $scope) {
        $this->_scope = $scope;
        $this->_scopeString = $this->_toScopeString($scope);
    }

    /**
     * @param string|array $scope
     */
    public function _addScope($scope){
        if (is_array($scope)){
            $this->_scope = array_merge($this->_scope, $scope);
            $this->_scopeString .= $this->_toScopeString($scope);
        } else {
            $this->_scope[] = $scope;
            $this->_scopeString .= $this->_toScopeString([$scope]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = array()){
        parent::log($level, $this->_scopeString . ' ' . $message, $context);
    }

    /*public function test(){
        if ($this->config['debug']){
            echo $this->_scope . 'logger test' . PHP_EOL;
        } else {
            echo $this->_scope . 'debug mode is off' . PHP_EOL;
        }
    }*/
}