<?php

namespace Core\Model;

use Core\Model\Tables\CustomQueries;
use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;
use \Core\Model\Tables\AccessTokens as TableAccessTokens;
use \Core\Model\Tables\Shops as TableShops;
use \Core\Model\Tables\Queries as TableComplexQueries;
use \Core\Model\Helper\Tokens;

class Shop
{
    protected $_id = false;

    /**
     * @var TableShops
     */
    protected $_shopsTable;

    /**
     * @var TableAccessTokens
     */
    protected $_accessTokensTable;

    /**
     * @var TableComplexQueries
     */
    protected $_complexQueriesTable;

    /*protected function __construct($shopId, TableShops $shopsTable, TableAccessTokens $accessTokensTable, TableComplexQueries $complexQueriesTable){
        $this->_id = $shopId;
        $this->_shopsTable = $shopsTable;
        $this->_accessTokensTable = $accessTokensTable;
        $this->_complexQueriesTable = $complexQueriesTable;
    }*/
    protected function __construct($shopId){
        $this->_id = $shopId;
    }

    /*public static function installShop($license, $url, $appVersion, TableShops $tableShops) {
        if ($tableShops->addShop($license, $url, $appVersion)) {
            return true;
        }
        return false;
    }*/

    /*public static function getInstance(
        $license,
        TableShops $tableShops,
        TableAccessTokens $tableAccessTokens,
        TableComplexQueries $tableComplexQueries
    ){
        if ($id = $tableShops->getShopId($license)) {
            return new static($id, $tableShops, $tableAccessTokens, $tableComplexQueries);
        }
        return false;
    }*/

    public static function getInstance(
        $license,
        TableShops $tableShops,
        TableAccessTokens $tableAccessTokens,
        TableComplexQueries $tableComplexQueries
    ){
        if ($id = $tableShops->getShopId($license)) {
            $shop = new static($id);
            $shop->_shopsTable = $tableShops;
            $shop->_accessTokensTable = $tableAccessTokens;
            $shop->_complexQueriesTable = $tableComplexQueries;
            return $shop;
        }
        return false;
    }

    public function getId(){
        return $this->_id;
    }

    /**
     * @var bool|string
     */
    protected $_url = false;

    /**
     * @var bool|\Core\Model\Helper\Tokens
     */
    protected $_token = false;

    /**
     * @return bool|Helper\Tokens
     */
    public function getToken(){
        if ($this->_token === false) {
            $this->_getInstalledShopData();
        }
        return $this->_token;
    }

    public function getUrl(){
        if ($this->_url === false) {
            $this->_getInstalledShopData();
        }
        return $this->_url;
    }

    protected function _getInstalledShopData(){
        if ($data = $this->_complexQueriesTable->getInstalledShopData($this->getId())) {
            $this->_url = $data['url'];
            $this->_token = new Tokens($data['access_token'], $data['refresh_token'], $data['expires']);
            return true;
        }
        return false;
    }

    /**
     * instantiate client resource
     * @param $appId
     * @param $appSecretKey
     * @return Client
     */
    public function instantiateSDKClient($appId, $appSecretKey)
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $this->getUrl(),
                'client_id' => $appId,
                'client_secret' => $appSecretKey
            ]
        );
        $client->setAccessToken($this->getToken()->accessToken());
        return $client;
    }

    /**
     * @param $appId
     * @param $appSecret
     * @return bool|Tokens
     * @throws \Exception
     */
    public function refreshToken($appId, $appSecret)
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(
            Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $this->getUrl(),
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'refresh_token' => $this->getToken()->refreshToken()
            ]
        );
        $tokens = $client->refreshTokens();
        $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

        try {
            /*
            $this->_model->updateTokens($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $this->getData('id'));
            */
            $table = new TableAccessTokens();
            $table->updateTokens(
                $this->getId(),
                [
                    $table::COLUMN_EXPIRES_AT => $expirationDate,
                    $table::COLUMN_ACCESS_TOKEN => $tokens['access_token'],
                    $table::COLUMN_REFRESH_TOKEN => $tokens['refresh_token']
                ]
            );
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        }

        $newToken = new Tokens($tokens['access_token'], $tokens['refresh_token'], $expirationDate);
        $this->_token = $newToken;
        return $this->getToken();
//        return [
//            'refresh_token' => $tokens['refresh_token'],
//            'access_token' => $tokens['access_token']
//        ];
    }
}