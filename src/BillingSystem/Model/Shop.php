<?php

namespace BillingSystem\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;


class Shop
{

    public function install($args){
        $db = \DbHandler::getDb();
        try {
            $tableShops = new \Core\Model\Tables\Shops();
            $db->beginTransaction();

            $update = false;
            try {
                /*
                $shopId = $this->_getShopId($arguments['shop']);
                */
                if ($id = $tableShops->getShopId($args['shop'])) {
                    $shopId = $id;
                } else {
                    // todo przenieść do osobnej metody
                    throw new \Exception('shop not found ' . $args['shop']);
                }
                $update = true;
            } catch (\Exception $exc) {
                // ignore
            }

            if ($update) {
                /*
                $shopStmtUpdate = $db->prepare('UPDATE shops SET shop_url = ?, version = ?, installed = 1 WHERE id = ?');
                $shopStmtUpdate->execute(array(
                    $arguments['shop_url'], $arguments['application_version'], $shopId
                ));
                */
                $tableShops->updateShop(
                    $shopId,
                    [
                        $tableShops::COLUMN_SHOP_URL => $args['shop_url'],
                        $tableShops::COLUMN_VERSION => $args['application_version']
                    ]
                );
            } else {
                // shop installation
                /*
                $shopStmtInsert = $db->prepare('INSERT INTO shops (shop, shop_url, version, installed) values (?,?,?,1)');
                $shopStmtInsert->execute(array(
                    $arguments['shop'], $arguments['shop_url'], $arguments['application_version']
                ));
                */
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
            $tableAccessTokens = new \Core\Model\Tables\AccessTokens();
            $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
            if ($update) {
                /*
                $tokensStmtUpdate = $db->prepare('UPDATE access_tokens SET expires_at = ?, access_token = ?, refresh_token = ? WHERE shop_id = ?');
                $tokensStmtUpdate->execute(array(
                    $expirationDate, $tokens['access_token'], $tokens['refresh_token'], $shopId
                ));
                */
                $tableAccessTokens->updateTokens(
                    $shopId,
                    [
                        $tableAccessTokens::COLUMN_EXPIRES_AT => $expirationDate,
                        $tableAccessTokens::COLUMN_ACCESS_TOKEN => $tokens['access_token'],
                        $tableAccessTokens::COLUMN_REFRESH_TOKEN => $tokens['refresh_token']
                    ]
                );
            } else {
                /*
                $tokensStmtInsert = $db->prepare('INSERT INTO access_tokens (shop_id, expires_at, access_token, refresh_token) VALUES (?,?,?,?)');
                $tokensStmtInsert->execute(array(
                    $shopId, $expirationDate, $tokens['access_token'], $tokens['refresh_token']
                ));
                */
                $tableAccessTokens->addToken($shopId, $expirationDate, $tokens['access_token'], $tokens['refresh_token']);
            }

            $db->commit();
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

    // todo
    public function billingInstall($arguments){
        try {
            $shopId = $this->_getShopId($arguments['shop']);
            // store payment event
            /*
            $stmt = \DbHandler::getDb()->prepare('INSERT INTO billings (shop_id) VALUES (?)');
            $stmt->execute(array(
                $shopId
            ));
            */
            $tableBillings = new \Core\Model\Tables\Billings();
            $tableBillings->addBilling($shopId);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

//    /**
//     * helper function for ID finding
//     * @param $shop
//     * @throws \Exception
//     * @return string
//     */
//    protected function _getShopId($shop)
//    {
//        $conn = \DbHandler::getDb();
//        $stmt = $conn->prepare('SELECT id FROM shops WHERE shop=?');
//
//        $stmt->execute(array(
//            $shop
//        ));
//        $id = $stmt->fetchColumn(0);
//        if (!$id) {
//            throw new \Exception('Shop not found: ' . $shop);
//        }
//
//        return $id;
//    }
    // todo getShopId

    public function upgrade($arguments)
    {
        try {
            $shopId = $this->_getShopId($arguments['shop']);

            // shop upgrade
            /*
            $shopStmt = \DbHandler::getDb()->prepare('UPDATE shops set version = ? WHERE id = '.(int)$shopId);
            $shopStmt->execute(array($arguments['application_version']));
            */
            $tableShops = new \Core\Model\Tables\Shops();
            $tableShops->updateShop($shopId, [$tableShops::COLUMN_VERSION => $arguments['application_version']]);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function uninstall($arguments)
    {
        try {
            $shopId = $this->_getShopId($arguments['shop']);


            // remove shop's references
            /*
            $conn = \DbHandler::getDb();
            $conn->query('UPDATE shops SET installed = 0 WHERE id=' . (int)$shopId);
            $tokens = $conn->prepare('UPDATE access_tokens SET access_token = ?, refresh_token = ? WHERE shop_id = ?');
            $tokens->execute(array(
                null, null, $shopId
            ));
            */
            $tableShops = new \Core\Model\Tables\Shops();
            $tableShops->updateShop($shopId,[$tableShops::COLUMN_INSTALLED => 0]);
            $tableTokens = new \Core\Model\Tables\AccessTokens();
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
    }

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
            /*
            $stmt = \DbHandler::getDb()->prepare('INSERT INTO subscriptions (shop_id, expires_at) VALUES (?,?)');
            $stmt->execute(array(
                $shopId, $expiresAt
            ));
            */
            $tableSubscriptions = new \Core\Model\Tables\Subscriptions();
            $tableSubscriptions->addSubscription($shopId, $expiresAt);

        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}