<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-09-30
 * Time: 20:48
 */

//namespace Core\Model;


class Shop
{
    public function getShopData($license) {
        $stmt = \DbHandler::getDb()->prepare('SELECT * FROM shops WHERE shop=:license');
        if (!$stmt->execute([':license' => $license])) {
            return false;
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * get installed shop info
     * @param $license
     * @return array|bool
     */
    public function getShopId($license)
    {
        $stmt = \DbHandler::getDb()->prepare('SELECT id FROM shops WHERE shop=:license');
        if (!$stmt->execute([':license' => $license])) {
            return false;
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['id'];

    }

    /**
     * get installed shop info
     * @param $license
     * @return array|bool
     */
    public function getAppVersion($license)
    {
        $stmt = \DbHandler::getDb()->prepare('SELECT version FROM shops WHERE shop=:license');
        if (!$stmt->execute([':license' => $license])) {
            return false;
        }
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['version'];
    }
}