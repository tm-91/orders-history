<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-12-07
 * Time: 20:40
 */

namespace BillingSystem\Controller;


class AbstractController
{
    /**
     * @var null|\BillingSystem\Model\BillingSystem
     */
    private $_billingSystem = null;

    public function __construct(){
        $this->_bootstrap();
    }

    protected function _bootstrap(){
        $this->_billingSystem = new \BillingSystem\Model\BillingSystem(
            new \Core\Model\Tables\Shops(),
            new \Core\Model\Tables\AccessTokens(),
            new \Core\Model\Tables\Billings(),
            new \Core\Model\Tables\Subscriptions()
        );
    }

    /**
     * @return null|\BillingSystem\Model\BillingSystem
     */
    public function billing(){
        return $this->_billingSystem;
    }
}