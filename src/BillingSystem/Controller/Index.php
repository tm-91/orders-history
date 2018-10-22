<?php
namespace BillingSystem\Controller;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

class Index
{
	/**
     * install action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - auth_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function installAction($arguments)
    {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: installAction' . PHP_EOL, FILE_APPEND);
        /** @var \PDO $db */
        $db = $this->db();
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: installAction - db = this->db()' . PHP_EOL, FILE_APPEND);
        try {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: installAction - try' . PHP_EOL, FILE_APPEND);            
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

    /**
     * client paid for the app
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function billingInstallAction($arguments)
    { 
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

	/**
     * upgrade action
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - application_version
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function upgradeAction($arguments)
    {
        try {
            $shopId = $this->getShopId($arguments['shop']);
    
            // shop upgrade
            $shopStmt = $this->db()->prepare('UPDATE shops set version = ? WHERE id = '.(int)$shopId);
            $shopStmt->execute(array($arguments['application_version']));
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * app is being uninstalled
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - hash
     * - timestamp
     *
     * @param array $arguments
     * @throws \Exception
     */
    public function uninstallAction($arguments)
    {
        try {
            $shopId = $this->getShopId($arguments['shop']);

            $conn = $this->db();

            // remove shop's references
            $conn->query('UPDATE shops SET installed = 0 WHERE id=' . (int)$shopId);
            $tokens = $conn->prepare('UPDATE access_tokens SET access_token = ?, refresh_token = ? WHERE shop_id = ?');
            $tokens->execute(array(
                null, null, $shopId
            ));
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * client paid for a subscription
     * arguments:
     * - action
     * - shop
     * - shop_url
     * - application_code
     * - subscription_end_time
     * - hash
     * - timestamp
     *
     * @param $arguments
     * @throws \Exception
     */
    public function billingSubscriptionAction($arguments)
    {
        try {
            $shopId = $this->getShopId($arguments['shop']);

            // make sure we convert timestamp correctly
            $expiresAt = date('Y-m-d H:i:s', strtotime($arguments['subscription_end_time']));

            if (!$expiresAt) {
                throw new \Exception('Malformed timestamp');
            }

            // save subscription event
            $stmt = $this->db()->prepare('INSERT INTO subscriptions (shop_id, expires_at) VALUES (?,?)');
            $stmt->execute(array(
                $shopId, $expiresAt
            ));
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * helper function for ID finding
     * @param $shop
     * @throws \Exception
     * @return string
     */
    protected function getShopId($shop)
    {
        $conn = $this->db();
        $stmt = $conn->prepare('SELECT id FROM shops WHERE shop=?');

        $stmt->execute(array(
            $shop
        ));
        $id = $stmt->fetchColumn(0);
        if (!$id) {
            throw new \Exception('Shop not found: ' . $shop);
        }

        return $id;
    }

    /**
     * return (and instantiate if needed) a db connection
     * @return \PDO
     */
    protected function db()
    { 
        static $handle = null;
        $config = \BillingSystem\App::getConfig();
        if (!$handle) {
            $handle = new \PDO(
                $config['db']['connection'],
                $config['db']['user'],
                $config['db']['pass']
            );
        }

        return $handle;
    }

}