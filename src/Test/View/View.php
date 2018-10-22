<?php
namespace Test\View;

class View
{
	protected $_params = null;
	protected $_controller = false;
	protected $_action = false;
	protected $_file = false;

	public function __construct($controller, $action, array $params = array()){
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_params = $params;
	}

	public function setParam($name, $value){
		$this->_params[$name] = $value;
	}

	public function unsetParam($name) {
		unset($this->_params[$name]);
	}

	public function isSetParam($name){
		return array_key_exists($this->_params[$name]);
	}

	public function getParam($name){
		if ($this->isSetParam($name)) {
			return $this->_params[$name];
		}
	}

	public function render(){
		/*static $called = false;

        if ($called) {
            return;
        }

        $called = true;

        $vars = $this->viewVars;
        $vars["_locale"] = $this->app->getLocale();

        // separate scopes
        $render = function () use ($tpl, $vars) {
            $template = explode("/", $tpl, 2);
            if (count($template) == 2) {
                if ($template[0] == "." or $template[0] == "..") {
                    return;
                }
                $__t = $template[0] . "/". basename($template[1], '.php');
            } elseif (count($template) == 1) {
                $__t = basename($template[0], '.php');
            } else {
                return;
            }
            unset($template, $tpl);
            unset($vars['__t']);
            extract($vars);
            require __DIR__ . '/../View/' . $__t . '.php';
        };*/

        extract($this->_params);
        require $this->_controller . DIRECTORY_SEPARATOR . $this->_action . '.php' ;
	}
}