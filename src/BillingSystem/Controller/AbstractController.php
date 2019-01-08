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
        $this->_billingSystem = new \BillingSystem\Model\BillingSystem();
    }

    /**
     * @return null|\BillingSystem\Model\BillingSystem
     */
    public function billing(){
        return $this->_billingSystem;
    }
}