<?php

namespace BillingSystem;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\Exception\Exception as ClientException;
use DreamCommerce\ShopAppstoreLib\Exception\HandlerException;
use DreamCommerce\ShopAppstoreLib\Handler;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;
use DreamCommerce\ShopAppstoreLib\Logger;

class App
{

    /**
     * @var null|Handler
     */
    protected $handler = null;

    /*
     * @var array configuration placeholder
     */
    protected static $config = array();

    public static $logger;
    /*
     * @param string $entrypoint
     * @throws \Exception
     */
//    public function __construct($config)
//    {
//        // if (empty($_POST['shop_url']) || empty($_POST['action'])) {
//        //     die;
//        // }
//        // $entrypoint = $_POST['shop_url'];
//
//        self::$config = $config;
//        /*try {
//            // instantiate a handler
//            $handler = $this->handler = new Handler(
//                $entrypoint, $config['appId'], $config['appSecret'], $config['appstoreSecret']
//            );
//
//            // subscribe to particular events
//            $handler->subscribe('install', array($this, 'installHandler'));
//            $handler->subscribe('upgrade', array($this, 'upgradeHandler'));
//            $handler->subscribe('billing_install', array($this, 'billingInstallHandler'));
//            $handler->subscribe('billing_subscription', array($this, 'billingSubscriptionHandler'));
//            $handler->subscribe('uninstall', array($this, 'uninstallHandler'));
//        } catch (HandlerException $ex) {
//            throw new \Exception('Handler initialization failed', 0, $ex);
//        }*/
//    }

    public function bootstrap(){
        self::$config = \Bootstraper::getConfig();

file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap' . PHP_EOL, FILE_APPEND);

        if (empty($_POST['shop_url']) || empty($_POST['action'])) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - empty($_POST[shop_url]) || empty($_POST[action])' . PHP_EOL, FILE_APPEND);            
            die;
        }
        try {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try' . PHP_EOL, FILE_APPEND);            
            // instantiate a handler
            $handler = $this->handler = new Handler(
                $_POST['shop_url'], self::$config['appId'], self::$config['appSecret'], self::$config['appstoreSecret']
            );
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try: new handler created' . PHP_EOL, FILE_APPEND);
            // subscribe to particular events
            $controller = new \BillingSystem\Controller\Index();
            $handler->subscribe('install', array($controller, 'installAction'));
            $handler->subscribe('upgrade', array($controller, 'upgradeAction'));
            $handler->subscribe('billing_install', array($controller, 'billingInstallAction'));
            $handler->subscribe('billing_subscription', array($controller, 'billingSubscriptionAction'));
            $handler->subscribe('uninstall', array($controller, 'uninstallAction'));
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - try: subscribe done' . PHP_EOL, FILE_APPEND);
        } catch (HandlerException $ex) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: bootstrap - catch: error' . PHP_EOL, FILE_APPEND);            
            throw new \Exception('Handler initialization failed', 0, $ex);
        }
    }

    /**
     * dispatches controller
     * @param array|null $data
     * @throws \Exception
     */
    public function dispatch($data = null)
    {

        try {
echo __METHOD__;
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch' . PHP_EOL, FILE_APPEND);
            $this->handler->dispatch($data);
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch - try this->handler->dispatch(data) done' . PHP_EOL, FILE_APPEND);            
        } catch (HandlerException $ex) {
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: dispatch - catch' . PHP_EOL, FILE_APPEND);            
            if ($ex->getCode() == HandlerException::HASH_FAILED) {
                throw new \Exception('Payload hash verification failed', 0, $ex);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function run($arguments){
file_put_contents(DREAMCOMMERCE_LOG_FILE, date('Y-m-d H:i:s') . ' [BillingSystem]: run' . PHP_EOL, FILE_APPEND);
        $this->bootstrap();
        $this->dispatch();
    }

    public static function getConfig(){
        return self::$config;
    }

    // /**
    //  * install action
    //  * arguments:
    //  * - action
    //  * - shop
    //  * - shop_url
    //  * - application_code
    //  * - application_version
    //  * - auth_code
    //  * - hash
    //  * - timestamp
    //  *
    //  * @param array $arguments
    //  * @throws \Exception
    //  */
    // public function installHandler($arguments)
    // {
    //     /** @var \PDO $db */
    //     $db = $this->db();

    //     try {
    //         $db->beginTransaction();

    //         $update = false;
    //         try {
    //             $shopId = $this->getShopId($arguments['shop']);
    //             $update = true;
    //         } catch (\Exception $exc) {
    //             // ignore
    //         }

    //         if ($update) {
    //             $shopStmtUpdate = $db->prepare('UPDATE shops SET shop_url = ?, version = ?, installed = 1 WHERE id = ?');
    //             $shopStmtUpdate->execute(array(
    //                 $arguments['shop_url'], $arguments['application_version'], $shopId
    //             ));
    //         } else {
    //             // shop installation
    //             $shopStmtInsert = $db->prepare('INSERT INTO shops (shop, shop_url, version, installed) values (?,?,?,1)');
    //             $shopStmtInsert->execute(array(
    //                 $arguments['shop'], $arguments['shop_url'], $arguments['application_version']
    //             ));

    //             $shopId = $db->lastInsertId();
    //         }

    //         // get OAuth tokens
    //         try {
    //             * @var OAuth $c 
    //             $c = $arguments['client'];
    //             $c->setAuthCode($arguments['auth_code']);
    //             $tokens = $c->authenticate();
    //         } catch (ClientException $ex) {
    //             throw new \Exception('Client error', 0, $ex);
    //         }

    //         // store tokens in db
    //         $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
    //         if ($update) {
    //             $tokensStmtUpdate = $db->prepare('UPDATE access_tokens SET expires_at = ?, access_token = ?, refresh_token = ? WHERE shop_id = ?');
    //             $tokensStmtUpdate->execute(array(
    //                 $expirationDate, $tokens['access_token'], $tokens['refresh_token'], $shopId
    //             ));
    //         } else {
    //             $tokensStmtInsert = $db->prepare('INSERT INTO access_tokens (shop_id, expires_at, access_token, refresh_token) VALUES (?,?,?,?)');
    //             $tokensStmtInsert->execute(array(
    //                 $shopId, $expirationDate, $tokens['access_token'], $tokens['refresh_token']
    //             ));
    //         }

    //         $db->commit();
    //     } catch (\PDOException $ex) {
    //         if ($db->inTransaction()) {
    //             $db->rollBack();
    //         }
    //         throw new \Exception('Database error', 0, $ex);
    //     } catch (\Exception $ex) {
    //         if ($db->inTransaction()) {
    //             $db->rollBack();
    //         }
    //         throw $ex;
    //     }
    // }

    // /**
    //  * client paid for the app
    //  * arguments:
    //  * - action
    //  * - shop
    //  * - shop_url
    //  * - application_code
    //  * - hash
    //  * - timestamp
    //  *
    //  * @param array $arguments
    //  * @throws \Exception
    //  */
    // public function billingInstallHandler($arguments)
    // {
    //     try {
    //         $shopId = $this->getShopId($arguments['shop']);

    //         // store payment event
    //         $stmt = $this->db()->prepare('INSERT INTO billings (shop_id) VALUES (?)');
    //         $stmt->execute(array(
    //             $shopId
    //         ));
    //     } catch (\PDOException $ex) {
    //         throw new \Exception('Database error', 0, $ex);
    //     } catch (\Exception $ex) {
    //         throw $ex;
    //     }
    // }

    // /**
    //  * upgrade action
    //  * arguments:
    //  * - action
    //  * - shop
    //  * - shop_url
    //  * - application_code
    //  * - application_version
    //  * - hash
    //  * - timestamp
    //  *
    //  * @param array $arguments
    //  * @throws \Exception
    //  */
    // public function upgradeHandler($arguments)
    // {
    //     try {
    //         $shopId = $this->getShopId($arguments['shop']);
    // 
    //         // shop upgrade
    //         $shopStmt = $this->db()->prepare('UPDATE shops set version = ? WHERE id = '.(int)$shopId);
    //         $shopStmt->execute(array($arguments['application_version']));
    //     } catch (\PDOException $ex) {
    //         throw new \Exception('Database error', 0, $ex);
    //     } catch (\Exception $ex) {
    //         throw $ex;
    //     }
    // }

    // /**
    //  * app is being uninstalled
    //  * arguments:
    //  * - action
    //  * - shop
    //  * - shop_url
    //  * - application_code
    //  * - hash
    //  * - timestamp
    //  *
    //  * @param array $arguments
    //  * @throws \Exception
    //  */
    // public function uninstallHandler($arguments)
    // {
    //     try {
    //         $shopId = $this->getShopId($arguments['shop']);

    //         $conn = $this->db();

    //         // remove shop's references
    //         $conn->query('UPDATE shops SET installed = 0 WHERE id=' . (int)$shopId);
    //         $tokens = $conn->prepare('UPDATE access_tokens SET access_token = ?, refresh_token = ? WHERE shop_id = ?');
    //         $tokens->execute(array(
    //             null, null, $shopId
    //         ));
    //     } catch (\PDOException $ex) {
    //         throw new \Exception('Database error', 0, $ex);
    //     } catch (\Exception $ex) {
    //         throw $ex;
    //     }
    // }

    // /**
    //  * client paid for a subscription
    //  * arguments:
    //  * - action
    //  * - shop
    //  * - shop_url
    //  * - application_code
    //  * - subscription_end_time
    //  * - hash
    //  * - timestamp
    //  *
    //  * @param $arguments
    //  * @throws \Exception
    //  */
    // public function billingSubscriptionHandler($arguments)
    // {
    //     try {
    //         $shopId = $this->getShopId($arguments['shop']);

    //         // make sure we convert timestamp correctly
    //         $expiresAt = date('Y-m-d H:i:s', strtotime($arguments['subscription_end_time']));

    //         if (!$expiresAt) {
    //             throw new \Exception('Malformed timestamp');
    //         }

    //         // save subscription event
    //         $stmt = $this->db()->prepare('INSERT INTO subscriptions (shop_id, expires_at) VALUES (?,?)');
    //         $stmt->execute(array(
    //             $shopId, $expiresAt
    //         ));
    //     } catch (\PDOException $ex) {
    //         throw new \Exception('Database error', 0, $ex);
    //     } catch (\Exception $ex) {
    //         throw $ex;
    //     }
    // }

    // /**
    //  * helper function for ID finding
    //  * @param $shop
    //  * @throws \Exception
    //  * @return string
    //  */
    // public function getShopId($shop)
    // {
    //     $conn = $this->db();
    //     $stmt = $conn->prepare('SELECT id FROM shops WHERE shop=?');

    //     $stmt->execute(array(
    //         $shop
    //     ));
    //     $id = $stmt->fetchColumn(0);
    //     if (!$id) {
    //         throw new \Exception('Shop not found: ' . $shop);
    //     }

    //     return $id;
    // }

    // /**
    //  * return (and instantiate if needed) a db connection
    //  * @return \PDO
    //  */
    // public function db()
    // {
    //     static $handle = null;
    //     if (!$handle) {
    //         $handle = new \PDO(
    //             $this->config['db']['connection'],
    //             $this->config['db']['user'],
    //             $this->config['db']['pass']
    //         );
    //     }

    //     return $handle;
    // }
}
