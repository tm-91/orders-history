<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-06
 * Time: 20:04
 */

namespace Core\Model\Tables;


class Subscriptions extends AbstractTable
{
    const COLUMN_SHOP_ID = 'shop_id';
    const COLUMN_EXPIRES_AT = 'expires_at';

    public function addSubscription($shopId, $expiresAt){
        $stmt = \DbHandler::getDb()->prepare('INSERT INTO `subscriptions` (`' . self::COLUMN_SHOP_ID . '`, `' . self::COLUMN_EXPIRES_AT . '`) VALUES (:id, :exp)');
        $stmt->bindValue(':id', $shopId, \PDO::PARAM_INT);
        $stmt->bindValue(':exp', $expiresAt);
        if ($stmt->execute() === false) {
        	\Bootstraper::logger()->error(
        		'Failed to add subscription' . PHP_EOL .
        		'shop id: ' . $shopId . PHP_EOL .
        		'expiration time: ' . $expiresAt
        	);
        	throw new \Exception('Failed to add subscription for shop id: ' . $shopId);
        }
    }
}