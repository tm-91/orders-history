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
        $model = new \BillingSystem\Model\Shop();
        $model->install($arguments);
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
//    public function billingInstallAction($arguments)
//    {
//        try {
//            $shopId = $this->getShopId($arguments['shop']);
//
//            // store payment event
//            $stmt = $this->db()->prepare('INSERT INTO billings (shop_id) VALUES (?)');
//            $stmt->execute(array(
//                $shopId
//            ));
//        } catch (\PDOException $ex) {
//            throw new \Exception('Database error', 0, $ex);
//        } catch (\Exception $ex) {
//            throw $ex;
//        }
//    }
    public function billingInstallAction($arguments)
    {
        $model = new \BillingSystem\Model\Shop();
        $model->billingInstall($arguments);
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
//    public function upgradeAction($arguments)
//    {
//        try {
//            $shopId = $this->getShopId($arguments['shop']);
//
//            // shop upgrade
//            $shopStmt = $this->db()->prepare('UPDATE shops set version = ? WHERE id = '.(int)$shopId);
//            $shopStmt->execute(array($arguments['application_version']));
//        } catch (\PDOException $ex) {
//            throw new \Exception('Database error', 0, $ex);
//        } catch (\Exception $ex) {
//            throw $ex;
//        }
//    }
    public function upgradeAction($arguments)
    {
        $model = new \BillingSystem\Model\Shop();
        $model->upgrade($arguments);
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
//    public function uninstallAction($arguments)
//    {
//        try {
//            $shopId = $this->getShopId($arguments['shop']);
//
//            $conn = $this->db();
//
//            // remove shop's references
//            $conn->query('UPDATE shops SET installed = 0 WHERE id=' . (int)$shopId);
//            $tokens = $conn->prepare('UPDATE access_tokens SET access_token = ?, refresh_token = ? WHERE shop_id = ?');
//            $tokens->execute(array(
//                null, null, $shopId
//            ));
//        } catch (\PDOException $ex) {
//            throw new \Exception('Database error', 0, $ex);
//        } catch (\Exception $ex) {
//            throw $ex;
//        }
//    }
    public function uninstallAction($arguments)
    {
        $model = new \BillingSystem\Model\Shop();
        $model->uninstall($arguments);
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
//    public function billingSubscriptionAction($arguments)
//    {
//        try {
//            $shopId = $this->getShopId($arguments['shop']);
//
//            // make sure we convert timestamp correctly
//            $expiresAt = date('Y-m-d H:i:s', strtotime($arguments['subscription_end_time']));
//
//            if (!$expiresAt) {
//                throw new \Exception('Malformed timestamp');
//            }
//
//            // save subscription event
//            $stmt = $this->db()->prepare('INSERT INTO subscriptions (shop_id, expires_at) VALUES (?,?)');
//            $stmt->execute(array(
//                $shopId, $expiresAt
//            ));
//        } catch (\PDOException $ex) {
//            throw new \Exception('Database error', 0, $ex);
//        } catch (\Exception $ex) {
//            throw $ex;
//        }
//    }
    public function billingSubscriptionAction($arguments)
    {
        $model = new \BillingSystem\Model\Shop();
        $model->billingSubscription($arguments);
    }

//    /**
//     * helper function for ID finding
//     * @param $shop
//     * @throws \Exception
//     * @return string
//     */
//    protected function getShopId($shop)
//    {
//        $conn = $this->db();
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

//    /**
//     * return (and instantiate if needed) a db connection
//     * @return \PDO
//     */
//    protected function db()
//    {
//        static $handle = null;
//        $config = \BillingSystem\App::getConfig();
//        if (!$handle) {
//            $handle = new \PDO(
//                $config['db']['connection'],
//                $config['db']['user'],
//                $config['db']['pass']
//            );
//        }
//
//        return $handle;
//    }

}