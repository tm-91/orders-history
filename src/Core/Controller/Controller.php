<?php

namespace Core\Controller;


abstract class Controller
{
    /**
     * @var \Core\AbstractApp
     */
    protected $_app;

    public function __construct($app){
        $this->_app = $app;
    }

}