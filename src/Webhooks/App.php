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
    public $_webhookData = false;

    /**
    * @var headers
    *
    */
    public $_headers = array();
    
    /**
     * @var array params from headers
     * 
     * OK
     * 
     */
    public $params = array();

    /**
     * @var \Core\Model\Entity\Shop
     */
    protected $_shop;

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

        $this->_headers = $this->getWebhookHeaders();
        $this->setParams([
            'id'        => $this->_headers['X-Webhook-Id'],
            'name'      => $this->_headers['X-Webhook-Name'],
            'shop'      => $this->_headers['X-Shop-Domain'],
            'license'   => $this->_headers['X-Shop-License'],
            'sha1'      => $this->_headers['X-Webhook-Sha1']
        ]);
        // checks request
        $this->validateWebhook();

        $this->_webhookData = $this->fetchRequestData();

        $this->_shop = new \Core\Model\Entity\Shop($this->getParam('license'));
        // detect if shop is already installed
        $this->_shop->getData();
    }

    public function shop(){
        return $this->_shop;
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
        return $this->_webhookData;
    }

    public function fetchRequestData($getRaw = false){
        $data = file_get_contents("php://input");
        if (!$getRaw) {
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
        $sha1 = sha1($this->getParam('id') . ':' . $secretKey . ':' . $this->fetchRequestData(true));

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
