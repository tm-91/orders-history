<?php


namespace Core\Model\Entity;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

class Shop
{
    protected $_model;
    protected $_license;

    protected $_shopData = false;

    public function __construct($license){
        $this->_license = $license;
        $this->_model = new \Core\Model\Shop();

    }

    public function getLicense(){
        return $this->_license;
    }

    public function getData($name = null) {
        if ($this->_shopData === false) {
            if ($shopData = $this->_model->getInstalledShopData($this->getLicense())) {
                $this->_shopData = $shopData;
            } else {
                throw new \Exception('Application is not installed in shop: ' . $this->getLicense());
            }
        }
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