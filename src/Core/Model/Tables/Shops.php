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
    const NAME = 'shops';
    const COLUMN_ID = 'id';
    const COLUMN_LICENSE = 'shop';
    const COLUMN_SHOP_URL = 'shop_url';
    const COLUMN_VERSION = 'version';
    const COLUMN_INSTALLED = 'installed';
    const COLUMN_TYPE = [
        self::COLUMN_VERSION => \PDO::PARAM_INT,
        self::COLUMN_INSTALLED => \PDO::PARAM_INT,
        self::COLUMN_ID => \PDO::PARAM_INT
    ];

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