<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-11-06
 * Time: 01:21
 */

namespace BillingSystem\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;


class Shop
{

    public function install($arguments){
        $db = \DbHandler::getDb();
        try {
            $db->beginTransaction();

            $update = false;
            try {
                $shopId = $this->getShopId($arguments['shop']);
                $update = true;
            } catch (\Exception $exc) {
                // ignore
            }

            if ($update) {
                $shopStmtUpdate = $db->prepare('UPDATE shops SET shop_url = ?, version = ?, installed = 1 WHERE id = ?');
                $shopStmtUpdate->execute(array(
                    $arguments['shop_url'], $arguments['application_version'], $shopId
                ));
            } else {
                // shop installation
                $shopStmtInsert = $db->prepare('INSERT INTO shops (shop, shop_url, version, installed) values (?,?,?,1)');
                $shopStmtInsert->execute(array(
                    $arguments['shop'], $arguments['shop_url'], $arguments['application_version']
                ));

                $shopId = $db->lastInsertId();
            }

            // get OAuth tokens
            try {
                /** @var OAuth $c */
                $c = $arguments['client'];
                $c->setAuthCode($arguments['auth_code']);
                $tokens = $c->authenticate();
            } catch (ClientException $ex) {
                throw new \Exception('Client error', 0, $ex);
            }

            // store tokens in db
            $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
            if ($update) {
                $tokensStmtUpdate = $db->prepare('UPDATE access_tokens SET expires_at = ?, access_token = ?, refresh_token = ? WHERE shop_id = ?');
                $tokensStmtUpdate->execute(array(
                    $expirationDate, $tokens['access_token'], $tokens['refresh_token'], $shopId
                ));
            } else {
                $tokensStmtInsert = $db->prepare('INSERT INTO access_tokens (shop_id, expires_at, access_token, refresh_token) VALUES (?,?,?,?)');
                $tokensStmtInsert->execute(array(
                    $shopId, $expirationDate, $tokens['access_token'], $tokens['refresh_token']
                ));
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

    public function billingInstall($arguments){
        try {
            $shopId = $this->getShopId($arguments['shop']);

            // store payment event
            $stmt = $this->db()->prepare('INSERT INTO billings (shop_id) VALUES (?)');
            $stmt->execute(array(
                $shopId
            ));
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}