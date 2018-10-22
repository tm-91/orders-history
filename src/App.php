<?php

abstract class App{

	public function dispatch(array $pathElements = null){
		// todo
        if (is_null($pathElements)){
            $pathElements = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathElements = ltrim($pathElements, '/');

            // check for parameter existence
            $query = $pathElements == 'webhook' ? 'index/index' : $pathElements;

            $pathElements = str_replace('\\', '', $pathElements);

            $pathElements = explode('/', $pathElements);

            if ($pathElements[0] == 'webhook'){
                array_shift($pathElements);
            } else {
                echo 'WEBHOOK DISPATCH() - COŚ JEST ZJEBANE';
                return false;
            }
        }

        $controllerName = ucfirst($queryData[0]);
        $class = '\\Controller\\'.$controllerName;

        if(!class_exists($class)){
            throw new Exception('Controller not found');
        }

        $params = $_GET;
        if(!empty($params['id'])){
            $params['id'] = @json_decode($params['id']);
        }

        $actionName = strtolower($queryData[1]).'Action';
        $controller = new $class($this, $params);
        if(!method_exists($controller, $actionName)){
            throw new Exception('Action not found');
        }

        $controller['shopUrl'] = $this->shopData['url'];

        $result = call_user_func_array(array($controller, $actionName), array_slice($queryData, 2));

        if($result!==false) {
            $viewName = strtolower($queryData[0]) . '/' . strtolower($queryData[1]);
            $controller->render($viewName);
        }
    }

    public abstract function bootstrap();

    public abstract function run();
}