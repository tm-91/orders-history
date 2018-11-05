<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-11-05
 * Time: 22:02
 */

namespace Core\Model\Entity;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

class Shop
{
    protected $_model;
    protected $_license;

    // a.access_token,
    // a.refresh_token,
    // s.shop_url as url,
    // a.expires_at as expires,
    // a.shop_id as id
    protected $_shopData = [];

    public function __construct($license){
        $this->_license = $license;
        $this->_model = new \Core\Model\Shop();
        if ($shopData = $this->_model->getInstalledShopData($this->_license)) {
            $this->_shopData = $shopData;
        } else {
            throw new \Exception('An application is not installed in this shop');
        }
    }

    public function getData($name = null) {
        if ($name === null) {
            return $this->_shopData;
        }
        if (isset($this->_shopData[$name])) {
            return $this->_shopData[$name];
        }
        // todo throw error
        return false;
    }

    public function getId(){
        return $this->getData('id');
    }

//    public function refreshToken($appId, $appSecretKey){
//        $this->_model->refreshToken(
//            $this->getData('id'),
//            $this->getData('url'),
//            $this->getData('refresh_token'),
//            $appId,
//            $appSecretKey
//        );
//    }

    public function refreshToken($appId, $appSecret)
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(
            Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $this->getData('url'),
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'refresh_token' => $this->getData('refresh_token')
            ]
        );
        $tokens = $client->refreshTokens();
        $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

        try {
            $this->_model->updateTokens($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $this->getData('id'));
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        }

        return [
            'refresh_token' => $tokens['refresh_token'],
            'access_token' => $tokens['access_token']
        ];
    }

    /**
     * instantiate client resource
     * @param $shopData
     * @return \DreamCommerce\ShopAppstoreLib\Client
     */
    public function instantiateSDKClient($appId, $appSecretKey)
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(Client::ADAPTER_OAUTH, array(
                'entrypoint' => $this->getData('url'),
                'client_id' => $appId,
                'client_secret' => $appSecretKey
            )
        );
        $client->setAccessToken($this->getData('access_token'));
        return $client;
    }

}