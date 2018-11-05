<?php
/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 19.10.18
 * Time: 10:26
 */

namespace Application\Model;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

class Shop
//    extends \Core\Model\Shop
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

    /////////////////////////////////////////////////////////////////////////////
    /**
     * refresh OAuth token
     * @param array $shopData
     * @return mixed
     * @throws \Exception
     */
    public function refreshToken($shopId, $entryPoint, $refreshToken, $appId, $appSecret)
    {
        /** @var OAuth $c */
        $c = Client::factory(
            Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $entryPoint,
                'client_id' => self::getConfig('appId'),
                'client_secret' => self::getConfig('appSecret'),
                'refresh_token' => $refreshToken
            ]
        );
        $tokens = $c->refreshTokens();
        $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

        try {
            $shopModel = new \Application\Model\Shop();
            $shopModel->updateTokens($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $shopId);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        }

        return [
            'refresh_token' => $tokens['refresh_token'],
            'access_token' => $tokens['access_token']
        ];
    }

    /**
     * instantiate client resource
     * @param $shopData
     * @return \DreamCommerce\ShopAppstoreLib\Client
     */
    public function instantiateClient($shopData)
    {
        /** @var OAuth $c */
        $c = Client::factory(Client::ADAPTER_OAUTH, array(
                'entrypoint' => $shopData['url'],
                'client_id' => self::getConfig('appId'),
                'client_secret' => self::getConfig('appSecret')
            )
        );
        $c->setAccessToken($shopData['access_token']);
        return $c;
    }
}