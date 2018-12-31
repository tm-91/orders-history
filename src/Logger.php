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
    public function log($level, $message, array $context = []){
        parent::log($level, $this->_scopeString . ' ' . $message, $context);
    }
}