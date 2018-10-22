<?php

namespace DreamCommerce\ShopAppstoreLib\Client;

use DreamCommerce\ShopAppstoreLib\Client\Exception\BasicAuthException;
use DreamCommerce\ShopAppstoreLib\Resource;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception;

/**
 * DreamCommerce requesting library
 *
 * @package DreamCommerce\ShopAppstoreLib\Client
 */
class BasicAuth extends Bearer
{
    /**
     * Authentication failure
     */
    const HTTP_ERROR_AUTH_FAILURE = "auth_failure";

    /**
     * Failure due to invalid IP being used
     */
    const HTTP_ERROR_AUTH_IP_NOT_ALLOWED = 'auth_ip_not_allowed';

    /**
     * Failure due to missing WebAPI credentials
     */
    const HTTP_ERROR_AUTH_WEBAPI_ACCESS_DENIED = 'auth_webapi_access_denied';

    /**
     * User login
     * @var null|string
     */
    protected $username = null;
    /**
     * User password
     * @var null|string
     */
    protected $password = null;

    /**
     * @param array $options
     * @throws BasicAuthException
     */
    public function __construct($options = array())
    {
        if(!is_array($options)) {
            throw new BasicAuthException('Adapter parameters must be in an array', Exception::PARAMETER_NOT_SPECIFIED);
        }

        foreach(array('username', 'password') as $reqParam) {
            if(!isset($options[$reqParam])) {
                throw new BasicAuthException('Parameter "' . $reqParam . '" is required', Exception::PARAMETER_NOT_SPECIFIED);
            }
        }

        $this->username = $options['username'];
        $this->password = $options['password'];

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($force = false)
    {
        if($this->accessToken !== null && !$force) {
            return false;
        }

        $res = $this->getHttpClient()->post(
            $this->entrypoint . '/auth',
            array(),
            array(
                'client_id' => $this->username,
                'client_secret' => $this->password
            ),
            array(
                'Accept-Language' => $this->getLocale() . ';q=0.8',
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        );

        if(!$res) {
            throw new BasicAuthException('General failure', BasicAuthException::GENERAL_FAILURE);
        } elseif(isset($res['data']['error'])) {
            $description = 'General failure';
            if(isset($res['data']['error_description'])) {
                $description = $res['data']['error_description'];
            }
            throw new BasicAuthException(array(
                'message' => $description,
                'http_error' => $res['data']['error']
            ), BasicAuthException::API_ERROR);
        }

        // automatically set token to the freshly requested
        $this->setAccessToken($res['data']['access_token']);

        return $res['data'];
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param null|string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
}