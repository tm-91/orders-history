<?php
namespace Webhooks;
/**
 * Class App
 */
class App_kopia extends \Core\AbstractApp
{

    /**
     * @var array sended data
     * 
     * OK
     * 
     */
    public $data = false;

    /**
    * @var headers
    *
    */
    public $headers = array();
    
    /**
     * @var array params from headers
     * 
     * OK
     * 
     */
    public $params = array();

    /**
     * @var current shop id
     * 
     * OK
     * 
     */
    public $shopId = '';

    /*
     * @var array configuration storage
     */
//    public $config = array();
    const MODULE_NAME = 'Webhooks';
    protected $_defaultController = 'Index';
    protected $_defaultAction = 'index';
    protected $_controllerNamespace = __NAMESPACE__ . '\Controller';
    protected $_viewNamespace = __NAMESPACE__ . '\View';
    protected $_modelNamespace = __NAMESPACE__ . '\Model';

    /**
     * @var \Core\Model\Shop
     */
    protected $_modelShop;

    /**
     * main application bootstrap
     * @throws \Exception
     */
    public function bootstrap()
    {
        parent::bootstrap();

        self::log('bootstrap start');
        $this->headers = $this->getWebhookHeaders();
        $this->setParams([
            'id'        => $this->headers['X-Webhook-Id'],
            'name'      => $this->headers['X-Webhook-Name'],
            'shop'      => $this->headers['X-Shop-Domain'],
            'license'   => $this->headers['X-Shop-License'],
            'sha1'      => $this->headers['X-Webhook-Sha1']
        ]);
        // checks request
        $this->validateWebhook();
        $this->setWebhookData($this->fetchRequestData());
        $this->_modelShop = new \Core\Model\Shop();
        // detect if shop is already installed
        $shopId = $this->_modelShop->getShopId($this->getParam('license'));
        if (!$shopId) {
            file_put_contents('./logs/webhooks.log', date("Y-m-d H:i:s"). ' License incorrect or application is not installed in shop.', FILE_APPEND);
            die();
        }
        
        $this->shopId = $shopId;
        self::log('bootstrap - end');
        // todo
        //$this->dispatch();    
        /*$controller = new \Controller\Webhook($this, $this->params);
        $actionName =  'statusAction';
        // fire
        $result = call_user_func_array(array($controller, $actionName), array($this->shopId, $this->data));*/
    }

    /*
     * dispatcher
     * @param array $urlElements
     * @throws \Exception
     */
    /*public function dispatch(array $urlElements = null)
    {
        self::log('App: dispatch - start');
        self::log('App: dispatch url elements:');
//for ($i = 0; $i <= count($urlElements); $i++) {
//file_put_contents(DREAMCOMMERCE_LOG_FILE, $i . ' => ' . $urlElements[$i] . PHP_EOL, FILE_APPEND);
//}
        self::log($urlElements);

        if (is_null($urlElements)){
            self::log('invalid argument passed to dispatch method');
            throw new \Exception('invalid argument passed to dispatcher method');
            // todo
//            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//            $path = trim($path, '/');
//            $path = str_replace('\\', '', $path);
//            $pathElements = $path == '' ? array() : explode('/', $path);

        }

        $controller = false;
        $controllerName = false;
        $action = false;
        $actionName = false;
        switch (count($urlElements)) {
            case 0:
                $controllerName = $this->_defaultController;
                $actionName = $this->_defaultAction;
                break;
            case 1:
                $controllerName = ucfirst(array_shift($urlElements));
                $actionName = $this->_defaultAction;
                break;
            default:
                $controllerName = ucfirst(array_shift($urlElements));
                $actionName = strtolower(array_shift($urlElements));
        }
        // self::controllerActionExists($controller, $action, true);
        $controller = $this->_controllerNamespace . '\\' . $controllerName;
        $action = $actionName . 'Action';

        self::log('App: controller: ' . $controllerName);
        if (!class_exists($controller)) {
            throw new \Exception('Controller "' . $controller . '" not found');
        }

        if (!is_callable(array($controller, $action))) {
            throw new \Exception('Action "' . $actionName . '" not found');
        }

        $controller = new $controller();
        $success = call_user_func_array(array($controller, $action), $urlElements);
        if ($success === false) {
            throw new \Exception('Failed to run method "' . $action . '" of class "' . $controller . '"');
        }
        self::log('App: dispatch - end');
    }*/

    public function run(array $pathArray = null){
        self::log('run');
        $this->bootstrap();
        $this->dispatch($pathArray['query']);
    }
    
    public function setParams(array $paramsArray){
        foreach ($paramsArray as $parameter => $value) {
            $this->params[$parameter] = $value;
        }
    }

    public function getParam($param){
        if (array_key_exists($param, $this->params)) {
            return $this->params[$param];
        } else {
            throw new \Exception('Webhook App param "' . $param . '" does not exists');
        }
    }

    public function removeParams(array $paramsArray){
        foreach ($paramsArray as $parameter) {
            if (array_key_exists($parameter, $this->params)){
                unset($this->params[$parameter]);
            } else {
                throw new \Exception('Webhook App param "' . $parameter . '" that you are trying to remove does not exists');
            }
        }   
    }

    public function getWebhookData(){
        return $this->data;
    }

    protected function setWebhookData(array $data) {
        $this->data = $data;
    }

    public function fetchRequestData($decodeJSON = true){
        $data = file_get_contents("php://input");
        if ($decodeJSON) {
            $data = json_decode($data, true);
        }
        return $data;
    }
    
    /**
     * checks variables and hash
     * @throws \Exception
     * 
     * OK
     * 
     */
    public function validateWebhook()
    {
        $secretKey = hash_hmac('sha512', $this->getParam('license') . ":" . self::getConfig('webhookSecretKey'), self::getConfig('appstoreSecret'));
        $sha1 = sha1($this->getParam('id') . ':' . $secretKey . ':' . $this->fetchRequestData(false));

        if ($sha1 != $this->getParam('sha1')) {
            self::log('Webhook validation failed. bad checksum: ' . $sha1);
            exit();
        }
        return true;
    }

    public function getWebhookHeaders(){
        if (!function_exists('getallheaders'))  {
            function getallheaders()
            {
                if (!is_array($_SERVER)) {
                    return array();
                }
        
                $headers = array();
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }
        $headers = getallheaders();
        return $headers;
    }

    // todo
    /*protected function getWebhookSecretKey(){

    }*/
    
    /**
     * @return bool
     */
    public function getDebug(){
        return self::$config['debug'];
    }

    /**
     * @return string
     */
    public function getShopDataToDebug(){
        $shopData = 'URL: ' . $this->params['shop'] . ' LICENSE: ' . $this->params['license'];
        return $shopData;
    }

    /*
     * get installed shop info
     * @param $license
     * @return array|bool
     */
    /*public function getShopId($license)
    {
        $db = $this->db();
        $stmt = $db->prepare('select id from shops where shop=:license');
        if (!$stmt->execute(array(':license' => $license))) {
            return false;
        }
        $result = $stmt->fetch();
        
        return $result['id'];

    }*/

    /*
     * get installed shop info
     * @param $license
     * @return array|bool
     */
    /*public function getAppVersion($license)
    {
        $db = $this->db();
        $stmt = $db->prepare('select version from shops where shop=:license');
        if (!$stmt->execute(array(':license' => $license))) {
            return false;
        }
        $result = $stmt->fetch();

        return $result['version'];

    }*/
    
    /*
     * instantiate db connection
     * @return \PDO
     */
    /*public function db()
    {
        static $handle = null;
        if (!$handle) {
            $handle = new \PDO(
                self::$config['db']['connection'],
                self::$config['db']['user'],
                self::$config['db']['pass']
            );
        }

        return $handle;
    }*/

    public static function escapeHtml($message){
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
