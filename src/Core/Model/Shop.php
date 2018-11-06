<?php
/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 19.10.18
 * Time: 10:26
 */

namespace Core\Model;

class Shop
{
    public function getInstalledShopData($shop)
    {
        $stmt = \DbHandler::getDb()->prepare('select a.access_token, a.refresh_token, s.shop_url as url, a.expires_at as expires, a.shop_id as id from access_tokens a join shops s on a.shop_id=s.id where s.shop=?');
        if (!$stmt->execute(array($shop))) {
            return false;
        }

        return $stmt->fetch();
    }

    public function updateTokens($refreshToken, $accessToken, $expirationDate, $shopId){
        $stmt = \DbHandler::getDb()->prepare('update access_tokens set refresh_token=?, access_token=?, expires_at=? where shop_id=?');
        $stmt->execute([$refreshToken, $accessToken, $expirationDate, $shopId]);
    }



}