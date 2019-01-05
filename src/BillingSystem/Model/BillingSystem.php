<?php

namespace BillingSystem\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

use \Core\Model\Tables\Billings as TableBillings;
use \Core\Model\Tables\Shops as TableShops;
use \Core\Model\Tables\AccessTokens as TableAccessTokens;
use \Core\Model\Tables\Subscriptions as TableSubscriptions;


class BillingSystem
{
    /**
     * @var bool|TableBillings
     */
    protected $_billingsTable = false;

    /**
     * @var bool|TableShops
     */
    protected $_shopsTable = false;

    /**
     * @var bool|TableAccessTokens
     */
    protected $_accessTokensTable = false;

    /**
     * @var bool|TableSubscriptions
     */
    protected $_subscriptionsTable = false;

    public function __construct(
        TableShops $tableShops,
        TableAccessTokens $tableAccessTokens,
        TableBillings $tableBillings,
        TableSubscriptions $tableSubscriptions
    ) {
        $this->_shopsTable = $tableShops;
        $this->_accessTokensTable = $tableAccessTokens;
        $this->_subscriptionsTable = $tableSubscriptions;
        $this->_billingsTable = $tableBillings;
    }

    public function installShop($args){
        $db = \DbHandler::getDb();
        try {
            $tableShops = $this->_shopsTable;
            $db->beginTransaction();

            $update = false;
            try {
                $shopId = $this->_getShopId($args['shop']);
                $update = true;
            } catch (\Exception $exc) {
                // ignore
            }

            if ($update) {
                $tableShops->updateShop(
                    $shopId,
                    [
                        $tableShops::COLUMN_SHOP_URL => $args['shop_url'],
                        $tableShops::COLUMN_VERSION => $args['application_version'],
                        $tableShops::COLUMN_INSTALLED => 1
                    ]
                );
            } else {
                // shop installation
                $tableShops->addShop($args['shop'], $args['shop_url'], $args['application_version']);
                $shopId = $db->lastInsertId();
            }

            // get OAuth tokens
            try {
                /** @var OAuth $c */
                $c = $args['client'];
                $c->setAuthCode($args['auth_code']);
                $tokens = $c->authenticate();
            } catch (ClientException $ex) {
                throw new \Exception('Client error', 0, $ex);
            }

            // store tokens in db
            $tableAccessTokens = $this->_accessTokensTable;
            $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
            if ($update) {
                $tableAccessTokens->updateTokens(
                    $shopId,
                    [
                        $tableAccessTokens::COLUMN_EXPIRES_AT => $expirationDate,
                        $tableAccessTokens::COLUMN_ACCESS_TOKEN => $tokens['access_token'],
                        $tableAccessTokens::COLUMN_REFRESH_TOKEN => $tokens['refresh_token']
                    ]
                );
            } else {
                $tableAccessTokens->addToken($shopId, $expirationDate, $tokens['access_token'], $tokens['refresh_token']);
            }

            $db->commit();

            return $shopId;
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

    public function billingInstall($arguments){
        try {
            $shopId = $this->_getShopId($arguments['shop']);
            // store payment event
            $this->_billingsTable->addBilling($shopId);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error during billing install', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * helper function for ID finding
     * @param $license
     * @return string
     * @throws \Exception
     */
    protected function _getShopId($license)
    {
        if ($id = $this->_shopsTable->getShopId($license)) {
            return $id;
        } else {
            throw new \Exception('did not found shop with license: ' . $license);
        }
    }

    public function upgrade($arguments)
    {
        try {
            $shopId = $this->_getShopId($arguments['shop']);

            // shop upgrade
            $tableShops = $this->_shopsTable;
            $tableShops->updateShop($shopId, [$tableShops::COLUMN_VERSION => $arguments['application_version']]);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /*public function uninstall($arguments)
    {
        try {
            $shopId = $this->_getShopId($arguments['shop']);
            // remove shop's references
            $tableShops = $this->_shopsTable;
            $tableShops->updateShop($shopId,[$tableShops::COLUMN_INSTALLED => 0]);
            $tableTokens = $this->_accessTokensTable;
            $tableTokens->updateTokens(
                $shopId,
                [
                    $tableTokens::COLUMN_ACCESS_TOKEN => null,
                    $tableTokens::COLUMN_REFRESH_TOKEN => null
                ]
            );
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }*/

    public function billingSubscription($arguments)
    {
        try {
            $shopId = $this->_getShopId($arguments['shop']);
            // make sure we convert timestamp correctly
            $expiresAt = date('Y-m-d H:i:s', strtotime($arguments['subscription_end_time']));
            if (!$expiresAt) {
                throw new \Exception('Malformed timestamp');
            }
            // save subscription event
            $this->_subscriptionsTable->addSubscription($shopId, $expiresAt);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}