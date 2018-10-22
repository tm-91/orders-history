<?php

namespace DreamCommerce\ShopAppstoreLib\Client;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception;
use DreamCommerce\ShopAppstoreLib\Resource;
use DreamCommerce\ShopAppstoreLib\Client\Exception\OAuthException;

/**
 * DreamCommerce requesting library
 *
 * @package DreamCommerce\ShopAppstoreLib\Client
 */
class OAuth extends Bearer
{
    /**
     * OAuth ID
     * @var null|string
     */
    protected $clientId = null;

    /**
     * OAuth secret
     * @var null|string
     */
    protected $clientSecret = null;

    /**
     * OAuth code
     * @var null|string
     */
    protected $authCode = null;

    /**
     * Refresh token
     * @var string
     */
    protected $refreshToken = null;

    /**
     * Scopes
     * @var array
     */
    protected $scopes = array();

    /**
     * @param array $options
     *
     * Example:
     * {
     *      entrypoint:     'http://shop.com',
     *      client_id:      'xxxxx',
     *      client_secret:  'xxxxx',
     *      auth_code:      'xxxxx',
     *      refresh_token:  'xxxxx',
     *      access_token:   'xxxxx'
     * }
     * @throws Exception
     * @throws OAuthException
     */
    public function __construct($options = array())
    {
        if(!is_array($options)) {
            throw new OAuthException('Adapter parameters must be an array', Exception::PARAMETER_NOT_SPECIFIED);
        }

        foreach(array('client_id', 'client_secret') as $reqParam) {
            if(!isset($options[$reqParam])) {
                throw new OAuthException('Parameter "' . $reqParam . '" is required', Exception::PARAMETER_NOT_SPECIFIED);
            }
        }

        $this->clientId = $options['client_id'];
        $this->clientSecret = $options['client_secret'];

        if(isset($options['auth_code'])) {
            $this->authCode = $options['auth_code'];
        }
        if(isset($options['access_token'])) {
            $this->accessToken = $options['access_token'];
        }
        if(isset($options['refresh_token'])) {
            $this->refreshToken = $options['refresh_token'];
        }

        parent::__construct($options);
    }

    /**
     * Authentication
     *
     * @param boolean $force
     * @throws \DreamCommerce\ShopAppstoreLib\Exception\Exception
     * @return \stdClass
     * Example output:
     * {
     *      access_token:   'xxxxx',
     *      refresh_token:  'xxxxx',
     *      expires_in:     '3600',
     *      token_type:     'bearer',
     *      scope:          'products_read,orders_read'
     * }
     */
    public function authenticate($force = false)
    {
        if($this->accessToken !== null && !$force) {
            return false;
        }

        $res = $this->getHttpClient()->post(
            $this->entrypoint . '/oauth/token',
            array(
                'code' => $this->getAuthCode()
            ), array(
                'grant_type' => 'authorization_code'
            ), array(
                'Authorization' => 'Basic ' . base64_encode($this->getClientId() . ':' . $this->getClientSecret()),
                'Accept-Language' => $this->getLocale() . ';q=0.8',
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        );

        if(!$res || isset($res['data']['error'])){
            throw new OAuthException($res['data']['error'], Exception::API_ERROR);
        }

        $this->accessToken = $res['data']['access_token'];
        $this->refreshToken = $res['data']['refresh_token'];
        $this->expiresIn = (int)$res['data']['expires_in'];
        $this->scopes = explode(',', $res['data']['scope']);

        return $res['data'];
    }

    /**
     * Refresh OAuth tokens
     *
     * @return array
     * @throws \DreamCommerce\ShopAppstoreLib\Exception\Exception
     */
    public function refreshTokens()
    {
        $res = $this->getHttpClient()->post($this->entrypoint . '/oauth/token', array(
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'refresh_token' => $this->getRefreshToken()
        ), array(
            'grant_type'=>'refresh_token'
        ), array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ));

        if(!$res || !empty($res['data']['error'])){
            throw new Exception($res['error'], Exception::API_ERROR);
        }

        $this->accessToken = $res['data']['access_token'];
        $this->refreshToken = $res['data']['refresh_token'];
        $this->expiresIn = (int)$res['data']['expires_in'];
        $this->scopes = explode(',', $res['data']['scope']);

        return $res['data'];
    }

    /**
     * @return null|string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param null|string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param null|string $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return string
     * @throws \DreamCommerce\ShopAppstoreLib\Exception\Exception
     */
    public function getAuthCode()
    {
        if($this->authCode === null) {
            throw new Exception('Parameter "auth_code" is required', Exception::PARAMETER_NOT_SPECIFIED);
        }

        return $this->authCode;
    }

    /**
     * @param null|string $authCode
     * @return $this
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * @return string
     * @throws \DreamCommerce\ShopAppstoreLib\Exception\Exception
     */
    public function getRefreshToken()
    {
        if($this->refreshToken === null) {
            throw new Exception('Parameter "refresh_token" is required', Exception::PARAMETER_NOT_SPECIFIED);
        }

        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}