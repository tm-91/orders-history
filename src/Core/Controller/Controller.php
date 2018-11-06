<?php

namespace Core\Controller;


abstract class Controller
{
    protected $_app;

    public function __construct($app){
        $this->_app = $app;
    }

}