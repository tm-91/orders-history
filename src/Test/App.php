<?php
namespace Test;
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 2018-10-10
 * Time: 13:18
 */
class App extends \Core\AbstractApp
{
    // protected $_controllerNamespace = static::MODULE_NAME . '\Controller';
	const MODULE_NAME = 'Test';

    public function run(array $params = null)
    {
        $this->bootstrap();
        $this->dispatch($params['query']);
    }


	protected $_calledController = 'Index';
	protected $_calledAction = 'index';

    public function getView(array $params = array()){
    	$namespace = '\\' . self::MODULE_NAME . '\\' . self::VIEW_NAMESPACE . '\\' . 'View';
    	return new $namespace($this->_calledController, ucfirst($this->_calledAction), $params);
    }
}