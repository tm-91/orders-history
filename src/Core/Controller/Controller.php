<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-10-07
 * Time: 22:18
 */

namespace Core\Controller;


abstract class Controller
{
    protected $_app;

    public function __construct($app){
        $this->_app = $app;
    }

}