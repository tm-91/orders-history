<?php
require 'src/bootstrap.php';
$a = new \Test\App();

$vars = [
	'varStr' => 'hahaaaa zmienna! DUUUPAAAAA!', 
	'varInt' => 15, 
	'varDuoble' => 12.5, 
	'varTrue' => true,
	'varFalse' => false,
	'ravNull' => null
];

$a->getView($vars)->render();