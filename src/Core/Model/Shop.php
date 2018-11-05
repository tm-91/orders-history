<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-09-30
 * Time: 20:48
 */

namespace Core\Model;


class Shop
{
    protected $_id;
    protected $_license;

    public function __construct($license){
        $this->_license = $license;
    }

    public function getData(){}
    public function updateTokens(){}
    public function getId(){}
    public function getLicense(){}

}