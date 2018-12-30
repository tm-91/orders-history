<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-06
 * Time: 15:59
 */

namespace Core\Model\Tables;


class Billings
{
    public function addBilling($shopId){
        // store payment event
        $stmt = \DbHandler::getDb()->prepare('INSERT INTO `billings` (`shop_id`) VALUES (:id);');
        $stmt->bindValue(':id', $shopId, \PDO::PARAM_INT);
        if ($stmt->execute() === false) {
        	throw new \Exception('Failed to add billing to for shop id: ' . $shopId);
        }
    }
}