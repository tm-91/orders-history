<?php
namespace Core;

interface AppInterface
{
	public function bootstrap();
	public function run(array $params = null);
}