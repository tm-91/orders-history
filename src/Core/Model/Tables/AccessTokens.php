<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-05
 * Time: 17:20
 */

namespace Core\Model\Tables;


class AccessTokens extends AbstractTable
{
    const COLUMN_SHOP_ID = 'shop_id';
    const COLUMN_EXPIRES_AT = 'expires_at';
    const COLUMN_ACCESS_TOKEN = 'access_token';
    const COLUMN_REFRESH_TOKEN = 'refresh_token';
    const COLUMN_TYPE = [
        self::COLUMN_SHOP_ID => \PDO::PARAM_INT
    ] ;

    public function updateTokens($shopId, array $fieldsAndValues){
        $stmt = \DbHandler::getDb()->prepare('UPDATE `access_tokens` SET ' . $this->_getParamsString($fieldsAndValues) . ' WHERE `shop_id` = :shopId');
        $stmt->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stmt = $this->_bindValues($stmt, $fieldsAndValues);
        return $stmt->execute();
    }

    public function addToken($shopId, $expirationDate, $accessToken, $refreshToken){
        $stmt = \DbHandler::getDb()->prepare('INSERT INTO `access_tokens` (`shop_id`, `expires_at`, `access_token`, `refresh_token`) VALUES (?,?,?,?)');
        $stmt->bindValue(':shopId', $shopId, \PDO::PARAM_INT);
        $stmt->bindValue(':expires', $expirationDate);
        $stmt->bindValue(':access', $accessToken);
        $stmt->bindValue(':refresh', $refreshToken);
        return $stmt->execute();
    }
}