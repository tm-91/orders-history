<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-07
 * Time: 17:52
 */

namespace Core\Model\Tables;


class Queries extends AbstractTable
{
    public function getInstalledShopData($shopId){
        $stmt = \DbHandler::getDb()->prepare('select a.access_token, a.refresh_token, s.shop_url as url, a.expires_at as expires from access_tokens a join shops s on a.shop_id=s.id where s.id=?;');
        if ($stmt->execute([$shopId])) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }
}