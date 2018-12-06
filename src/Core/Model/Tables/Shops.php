<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-05
 * Time: 17:21
 */

namespace Core\Model\Tables;


class Shops extends AbstractTable
{
//    public function getInstalledShopData($shop)
//    {
//        $stmt = \DbHandler::getDb()->prepare('select a.access_token, a.refresh_token, s.shop_url as url, a.expires_at as expires, a.shop_id as id from access_tokens a join shops s on a.shop_id=s.id where s.shop=?');
//        if (!$stmt->execute(array($shop))) {
//            return false;
//        }
//
//        return $stmt->fetch();
//    }

    /**
     * @param $license
     * @return bool|string
     */
    public function getShopId($license)
    {
        $stmt = \DbHandler::getDb()->prepare('SELECT `id` FROM `shops` WHERE `shop`=?');
        if ($stmt->execute([$license])){
            return $stmt->fetchColumn(0);
        }
        return false;
    }

    /*public function updateShop($shopId, $shopUrl = null, $appVersion = null){
        $col = [];
        if ($shopUrl !== null) {
            $col[] = ['`shop_url` = :url'];
        }
        if ($appVersion !== null) {
            $col[] = ['`version` = :version'];
        }
        $stmt = \DbHandler::getDb()->prepare('UPDATE `shops` SET ' . implode(', ', $col) . ', `installed` = 1 WHERE `id` = :id');
        $stmt->bindValue(':id', $shopId, \PDO::PARAM_INT);
        if ($shopUrl !== null){
            $stmt->bindValue(':url', $shopUrl);
        }
        if ($appVersion !== null) {
            $stmt->bindValue(':version', $appVersion, \PDO::PARAM_INT);
        }
        return $stmt->execute();
    }*/

    const COLUMN_SHOP_URL = 'shop_url';
    const COLUMN_VERSION = 'version';
    const COLUMN_INSTALLED = 'installed';
    const COLUMN_TYPE = [
        self::COLUMN_VERSION => \PDO::PARAM_INT,
        self::COLUMN_INSTALLED => \PDO::PARAM_INT
    ];

    public function updateShop($shopId, array $fields){
        $stmt = \DbHandler::getDb()->prepare('UPDATE `shops` SET ' . $this->_getParamsString($fields) . ' WHERE `id` = :id');
        $stmt->bindValue(':id', $shopId, \PDO::PARAM_INT);
        $stmt = $this->_bindValues($stmt, $fields);
        return $stmt->execute();
    }




    public function addShop($license, $url, $appVersion){
        $stmt = \DbHandler::getDb()->prepare('INSERT INTO `shops` (`shop`, `shop_url`, `version`, `installed`) VALUES (:license, :url, :version, 1)');
        $stmt->bindValue(':license', $license);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':version', $appVersion, \PDO::PARAM_INT);
        return $stmt->execute();
    }

}