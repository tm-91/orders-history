<?php
namespace Application;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;

//use Core\AbstractApp;


class App extends \Core\AbstractApp
{

    /**
     * @var null|\DreamCommerce\ShopAppstoreLib\Client
     */
    protected $client = null;
    /**
     * @var string default locale
     */
    protected $locale = 'pl_PL';

    /**
     * @var array current shop metadata
     */
    public $shopData = array();

    /*
     * @var array configuration storage
     */
//    public $config = array();

    public $moduleName = 'Application';

    public $urlArray = null;

    /**
     * @var array url parameters storage
     */
    protected $params = array();

    protected $_defaultController = 'Index';
    protected $_defaultAction = 'index';
    protected $_controllerNamespace = __NAMESPACE__ . '\Controller';
    protected $_viewNamespace = __NAMESPACE__ . '\View';
    protected $_modelNamespace = __NAMESPACE__ . '\Model';

    const MODULE_NAME = 'Application';

    /**
     * instantiate
     * @param array $config
     */
    public function __construct()
    {
        // parent::__construct();
        if (empty($_GET['locale'])) {
            die();
        }
        setlocale(LC_ALL, basename($_GET['locale']));

//        $this->config = $config;
    }

    public function setUrlArray(array $url)
    {
        $this->urlArray = $url;
    }

    /**
     * main application bootstrap
     * @throws \Exception
     */
    public function bootstrap()
    {
        parent::bootstrap();

        $this->setupParams($_GET);

        // check request hash and variables
        $this->validateRequest();

        $this->locale = basename($this->getParam('translations'));

        // detect if shop is already installed
        $shopModel = new Model\Shop();
        $shopData = $shopModel->getInstalledShopData($this->getParam('shop'));
        if (!$shopData) {
            throw new \Exception('An application is not installed in this shop');
        }

        $this->shopData = $shopData;

        // refresh token
        if (strtotime($shopData['expires']) - time() < 86400) {
            $this->refreshToken(
                $shopData['id'],
                $shopData['url'],
                $shopData['refresh_token']
            );
        }


        // instantiate SDK client
        $this->client = $this->instantiateClient($shopData);

        // fire
        // $this->dispatch();
    }

    /*
     * dispatcher
     * @param array $urlElements
     * @throws \Exception
     */
//    public function dispatch(array $urlElements = null)
//    {
//        if (is_null($urlElements)){
//            throw new \Exception('invalid argument passed to dispatcher method');
//            // todo
////            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
////            $path = trim($path, '/');
////            $path = str_replace('\\', '', $path);
////            $pathElements = $path == '' ? array() : explode('/', $path);
//
//        }
//
//        $controller = false;
//        $controllerName = false;
//        $action = false;
//        $actionName = false;
//        switch (count($urlElements)) {
//            case 0:
//                $controllerName = $this->_defaultController;
//                $actionName = $this->_defaultAction;
//                break;
//            case 1:
//                $controllerName = ucfirst(array_shift($urlElements));
//                $actionName = $this->_defaultAction;
//                break;
//            default:
//                $controllerName = ucfirst(array_shift($urlElements));
//                $actionName = strtolower(array_shift($urlElements));
//        }
//        // self::controllerActionExists($controller, $action, true);
//        $controller = $this->_controllerNamespace . '\\' . $controllerName;
//        $action = $actionName . 'Action';
//
//        if (!class_exists($controller)) {
//            throw new \Exception('Controller "' . $controller . '" not found');
//        }
//
//        if (!is_callable(array($controller, $action))) {
//            throw new \Exception('Action "' . $actionName . '" not found');
//        }
//
//        // check for parameter existence
//        /*$query = empty($_GET['q']) ? 'index/index' : $_GET['q'];
//        if ($query[0]=='/') {
//            $query = substr($query, 1);
//        }
//
//        $query = str_replace('\\', '', $query);
//
//        $queryData = explode('/', $query);*/
//        $params = $_GET;
//
//        // $controllerName = ucfirst($queryData[0]);
//        // $class = '\\Application\\Controller\\'.$controllerName;
//
//        // if (!class_exists($class)) {
//            // throw new \Exception('Controller not found');
//        // }
//
//        // $params = $_GET;
//        if (!empty($params['id'])) {
//            $params['id'] = @json_decode($params['id']);
//        }
//
//        // $actionName = strtolower($queryData[1]).'Action';
//        $controller = new $controller($this, $params);
//        // if (!method_exists($controller, $actionName)) {
//            // throw new \Exception('Action not found');
//        // }
//
//        $controller['shopUrl'] = $this->shopData['url'];
//
//        $success = call_user_func_array(array($controller, $action), $urlElements);
//        if ($success === false) {
//            throw new \Exception('Failed to run method "' . $action . '" of class "' . $controller . '"');
//        }
//
//        if ($success !== false) {
////            $viewName = strtolower($queryData[0]) . '/' . strtolower($queryData[1]);
//            $viewName = $controllerName . DIRECTORY_SEPARATOR . $actionName;
//            $controller->render($viewName);
//        }
//    }

    public function run(array $data = null)
    {
        $this->bootstrap();
        $this->dispatch($data['query']);
    }

    // todo - wyjebać
    /*protected function getControllerNamespace($controllerName){
        return $this->_controllerNamespace . '\\' . $controllerName;
    }*/

    // todo - wyjebać
    /*protected function getActionName($action){
        return $action . 'Action';
    }*/

    // todo - wyjebać
    /*public static function controllerExists($controllerName, $throwException = false){
        $controller = $this->getControllerNamespace($controllerName);
        $exists = class_exists($controller);
        if (!$exists && $throwException) {
            throw new \Exception('Controller "' . $controller . '" not found');
        }
        return $exists;
    }*/

    // todo - wyjebać
    /*public static function controllerActionExists($controllerName, $actionName, $throwExecption = false){
        self::controllerExists($controllerName, true);
        $controller = $this->getControllerNamespace($controllerName);
        $exists = is_callable($controller);
        if (!$exists && $throwException) {
            throw new \Exception('Action "' . $actionName . '" not found');
        }
        return $exists;
    }*/

    // todo pomyśleć co z tym - wyjebać czy nie
    public function setupParams($params)
    {
        /*$this->params = array(
            'place' => $_GET['place'],
            'shop' => $_GET['shop'],
            'timestamp' => $_GET['timestamp'],
        );*/
        $this->params = $params;
    }

    /*public function getParams(){
        return $this->params;
    }*/
    public function getParam($param = null)
    {
        if (is_null($param)) {
            return $this->params;
        }
        if (isset($this->params[$param])) {
            return $this->params[$param];
        } else {
            throw new \Exception('Parameter "' . $param . '" is not set');
        }
    }

//    public function getConfig($name = null){
//        if (is_null($name)) {
//            return $this->config;
//        }
//        if (isset($this->config[$name])) {
//            return $this->config[$name];
//        } else {
//            throw new \Exception('Configuration field "' . $name . '" that you trying to get does not exists');
//        }
//    }

    /**
     * instantiate client resource
     * @param $shopData
     * @return \DreamCommerce\ShopAppstoreLib\Client
     */
    public function instantiateClient($shopData)
    {
        /** @var OAuth $c */
        $c = Client::factory(Client::ADAPTER_OAUTH, array(
                'entrypoint' => $shopData['url'],
                'client_id' => self::getConfig('appId'),
                'client_secret' => self::getConfig('appSecret')
            )
        );
        $c->setAccessToken($shopData['access_token']);
        return $c;
    }

    /**
     * get client resource
     * @throws \Exception
     * @return \DreamCommerce\ShopAppstoreLib\Client|null
     */
    public function getClient()
    {
        if ($this->client === null) {
            throw new \Exception('Client is NOT instantiated');
        }

        return $this->client;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * refresh OAuth token
     * @param array $shopData
     * @return mixed
     * @throws \Exception
     */
    public function refreshToken($shopId, $entryPoint, $refreshToken)
    {
        /** @var OAuth $c */
        $c = Client::factory(
            Client::ADAPTER_OAUTH,
            [
                'entrypoint' => $entryPoint,
                'client_id' => self::getConfig('appId'),
                'client_secret' => self::getConfig('appSecret'),
                'refresh_token' => $refreshToken
            ]
        );
        $tokens = $c->refreshTokens();
        $expirationDate = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

        try {
//            $db = $this->db();
//            $stmt = $db->prepare('update access_tokens set refresh_token=?, access_token=?, expires_at=? where shop_id=?');
//            $stmt->execute(array($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $shopData['id']));

            $shopModel = new \Application\Model\Shop();
            $shopModel->updateTokens($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $shopId);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        }

//        $shopData['refresh_token'] = $tokens['refresh_token'];
//        $shopData['access_token'] = $tokens['access_token'];

        return [
            'refresh_token' => $tokens['refresh_token'],
            'access_token' => $tokens['access_token']
        ];
    }

    /**
     * checks variables and hash
     * @throws \Exception
     */
    public function validateRequest()
    {
        if (empty($this->getParam('translations'))) {
            throw new \Exception('Invalid request');
        }

        $params = array(
            'place' => $this->getParam('place'),
            'shop' => $this->getParam('shop'),
            'timestamp' => $this->getParam('timestamp'),
        );

        // tego ksorta chyba bedzie trzeba wyjebać - na co to komu
        ksort($params);
        $parameters = array();
        foreach ($params as $k => $v) {
            $parameters[] = $k . "=" . $v;
        }
        $p = join("&", $parameters);


        $hash = hash_hmac('sha512', $p, $this->getConfig('appstoreSecret'));

        if ($hash != $this->getParam('hash')) {
            throw new \Exception('Invalid request');
        }
    }

    /*
     * get installed shop info
     * @param $shop
     * @return array|bool
     */
//    public function getShopData($shop)
//    {
//        $db = $this->db();
//        $stmt = $db->prepare('select a.access_token, a.refresh_token, s.shop_url as url, a.expires_at as expires, a.shop_id as id from access_tokens a join shops s on a.shop_id=s.id where s.shop=?');
//        if (!$stmt->execute(array($shop))) {
//            return false;
//        }
//
//        return $stmt->fetch();
//    }

    /*
     * instantiate db connection
     * @return \PDO
     */
//    public function db()
//    {
//        static $handle = null;
//        if (!$handle) {
//            $handle = new \PDO(
//                $this->config['db']['connection'],
//                $this->config['db']['user'],
//                $this->config['db']['pass']
//            );
//        }
//
//        return $handle;
//    }

    /**
     * shows more friendly exception message
     * @param \Exception $ex
     */
    public function handleException(\Exception $ex)
    {
        $message = $ex->getMessage();
        require __DIR__ . '/../view/exception.php';
    }

    public static function escapeHtml($message)
    {
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }

    public static function getUrl($url)
    {
        $params = array();
        parse_str($_SERVER['QUERY_STRING'], $params);
        $params['q'] = $url;
        $query = http_build_query($params);
        return $url . '?' . $query;
    }

    /**
     * @param $controller
     * @param $action
     * @param array $params contains parameters passed to view
     */
    public function render1($controller, $action, array $params)
    {
        static $called = false;

        if ($called) {
            return;
        }

        $called = true;

        $params["_locale"] = $this->getLocale();

        require __DIR__ . DIRECTORY_SEPARATOR .
            self::VIEW_NAMESPACE . DIRECTORY_SEPARATOR .
            $controller . DIRECTORY_SEPARATOR .
            $action . '.php';
    }

    public function testRender($controller, $action){
        $a = __DIR__ . DIRECTORY_SEPARATOR .
            self::VIEW_NAMESPACE . DIRECTORY_SEPARATOR .
            $controller . DIRECTORY_SEPARATOR .
            $action . '.php';
        var_dump($a);
    }


    public function getView(array $params = array()){
        $var["_locale"] = $this->getLocale();
        $namespace = '\\' . self::MODULE_NAME . '\\' . self::VIEW_NAMESPACE . '\\' . 'View';
        return new $namespace($this->_calledController, ucfirst($this->_calledAction), $params);
    }
}
