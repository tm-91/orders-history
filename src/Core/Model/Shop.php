<?php

namespace Core\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;
use \Core\Model\Tables\AccessTokens as TableAccessTokens;
use \Core\Model\Tables\Shops as TableShops;
use \Core\Model\Tables\Queries as TableComplexQueries;
use \Application\Model\Tables\Order as TableOrders;
use \Core\Model\Helper\Tokens;

class Shop
{
    protected $_id = false;

    /**
     * @var TableShops
     */
    protected $_tableShops;

    /**
     * @var TableAccessTokens
     */
    protected $_tableAccessTokens;

    /**
     * @var TableComplexQueries
     */
    protected $_customQueries;

    /**
     * @var TableOrders
     */
    protected $_tableOrders;

    protected $_configs;

    public function __construct($shopId){
        $this->_id = $shopId;
        $this->_bootstrap();
    }

    protected function _bootstrap(){
        $this->_tableShops = new TableShops();
        $this->_tableAccessTokens = new TableAccessTokens();
        $this->_tableOrders = new TableOrders();
        $this->_customQueries = new TableComplexQueries();
        $this->_configs = \Bootstraper::getConfig();
    }

    protected function _getConfig($key = null){
        if ($key !== null) {
            return $this->_configs[$key];
        }
        return $this->_configs;
    }

    public static function getInstance($license){
        $tableShops = new TableShops();
        if ($id = $tableShops->getShopId($license)) {
            return new self($id);
        }
        throw new \Exception('Did not found shop with license: ' . $license);
    }

    /**
     * @param $license
     * @param $url
     * @param $applicationVersion
     * @param $client
     * @param $authCode
     * @return Shop
     * @throws \Exception
     */
    public static function install($license, $url, $applicationVersion, $client, $authCode){
        $db = \DbHandler::getDb();
        try {
            $db->beginTransaction();

            $update = false;
            try {
                $shop = self::getInstance($license);
                $update = true;
            } catch (\Exception $ex) {
                // ignore
            }

            $tableShops = new TableShops();
            if ($update) {
                // app is already installed in shop
                $tableShops->updateShop(
                    $shop->getId(),
                    [
                        TableShops::COLUMN_SHOP_URL => $url,
                        TableShops::COLUMN_VERSION => $applicationVersion,
                        TableShops::COLUMN_INSTALLED => 1
                    ]
                );
            } else {
                // shop installation
                $tableShops->addShop($license, $url, $applicationVersion);
                $shopId = $db->lastInsertId();
                $shop = new self($shopId);
            }

            // get OAuth tokens
            try {
                /** @var OAuth $c */
                $c = $client;
                $c->setAuthCode($authCode);
                $tokens = $c->authenticate();
            } catch (ClientException $ex) {
                throw new \Exception('Client error', 0, $ex);
            }

            // store tokens in db
            $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
            $tableAccessTokens = new TableAccessTokens();
            if ($update) {
                $tableAccessTokens->updateTokens(
                    $shop->getId(),
                    [
                        $tableAccessTokens::COLUMN_EXPIRES_AT => $expirationDate,
                        $tableAccessTokens::COLUMN_ACCESS_TOKEN => $tokens['access_token'],
                        $tableAccessTokens::COLUMN_REFRESH_TOKEN => $tokens['refresh_token']
                    ]
                );
            } else {
                $tableAccessTokens->addToken($shop->getId(), $expirationDate, $tokens['access_token'], $tokens['refresh_token']);
            }
            $db->commit();
            return $shop;
        } catch (\PDOException $ex) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

    public static function isInstalled($id){
        $tableShops = new TableShops();
        return $tableShops->select(
            [TableShops::COLUMN_INSTALLED],
            [TableShops::COLUMN_ID => $id]
        ) == 1 ? true : false;
    }

    public static function isInstalledByLicense($license) {
        $tableShops = new TableShops();
        return $tableShops->select(
            [TableShops::COLUMN_INSTALLED],
            [TableShops::COLUMN_LICENSE => $license]
        ) == 1 ? true : false;
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
        if ($data = $this->_customQueries->getInstalledShopData($this->getId())) {
            $this->_url = $data['url'];
            $this->_token = new Tokens($data['access_token'], $data['refresh_token'], $data['expires']);
            return true;
        }
        throw new \Exception('shop id: ' . $this->getId() . ' is not installed');
    }

    /**
     * instantiate client resource
     * @return Client
     */
    public function instantiateSDKClient()
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $this->getUrl(),
                'client_id' => $this->_getConfig('appId'),
                'client_secret' => $this->_getConfig('appSecret')
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
    public function refreshToken()
    {
        /**
         * @var OAuth $client
         */
        $client = Client::factory(
            Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $this->getUrl(),
                'client_id' => $this->_getConfig('appId'),
                'client_secret' => $this->_getConfig('appSecret'),
                'refresh_token' => $this->getToken()->refreshToken()
            ]
        );
        $tokens = $client->refreshTokens();
        $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

        try {
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
    }

    public function addOrder($orderId, $currentState){
        return $id = \Application\Model\Order::addNewOrder($this->getId(), $orderId, $currentState);
    }

    public function getOrder($orderId){
        return \Application\Model\Order::getInstance($this->getId(), $orderId);
    }

    public function removeOrdersAndHistory(){
        $this->_tableOrders->removeShopOrders($this->getId());
    }

    public function uninstall(){
        $this->removeOrdersAndHistory();
        $this->_tableShops->updateShop($this->getId(),[TableShops::COLUMN_INSTALLED => 0]);
        $this->_tableAccessTokens->updateTokens(
            $this->getId(),
            [
                TableAccessTokens::COLUMN_ACCESS_TOKEN => null,
                TableAccessTokens::COLUMN_REFRESH_TOKEN => null
            ]
        );
    }

    public function upgrade(array $upgradeData){
        $this->_tableShops->updateShop(
            $this->getId(),
            [
                TableShops::COLUMN_VERSION => $upgradeData['application_version']
            ]
        );
    }
}