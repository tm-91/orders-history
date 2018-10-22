<?php
namespace Webhooks;
/**
 * Class App
 */
class App extends \Core\AbstractApp
{

    /**
     * @var array sended data
     * 
     * OK
     * 
     */
    public $_data = false;

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

    const MODULE_NAME = 'Webhooks';

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
    }

    public function run(array $pathArray = null){
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
        return $this->_data;
    }

    protected function setWebhookData(array $data) {
        $this->_data = $data;
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

    public static function escapeHtml($message){
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
