<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-07
 * Time: 13:58
 */

namespace Core\Model\Helper;


class Tokens
{
    protected $_accessToken = false;
    protected $_refreshToken = false;
    protected $_expiresAt = false;

    public function __construct($accessToken, $refreshToken, $expiresAt){
        $this->_accessToken = $accessToken;
        $this->_refreshToken = $refreshToken;
        $this->_expiresAt = $expiresAt;
    }

    public function accessToken()
    {
        return $this->_accessToken;
    }

    public function refreshToken()
    {
        return $this->_refreshToken;
    }

    public function expiresAt()
    {
        return $this->_expiresAt;
    }

}