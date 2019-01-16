<?php
namespace Webhooks;

use \Core\Model\Shop;
/**
 * Class App
 */
class App extends \Core\AbstractApp
{
    /**
    * @var headers
    *
    */
    protected $_headers = array();

    /**
     * @var Shop
     */
    protected $_shop;

    const MODULE_NAME = 'Webhooks';

    /**
     * main application bootstrap
     * @throws \Exception
     */
    public function bootstrap()
    {
        parent::bootstrap();

        $this->_headers = $this->getWebhookHeaders();

        $this->_params = [
            'id'        => $this->_headers['X-Webhook-Id'],
            'name'      => $this->_headers['X-Webhook-Name'],
            'shop'      => $this->_headers['X-Shop-Domain'],
            'license'   => $this->_headers['X-Shop-License'],
            'sha1'      => $this->_headers['X-Webhook-Sha1']
        ];
        // checks request
        $this->validateWebhook();

        $this->_shop = Shop::getInstance($this->getParam('license'));
    }

    /**
     * @return Shop
     */
    public function shop(){
        return $this->_shop;
    }

    public function run(array $pathArray = null){
        $this->bootstrap();
        $this->dispatch($pathArray['query']);
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
        $sha1 = sha1($this->getParam('id') . ':' . $secretKey . ':' . $this->getResponseData(true));

        if ($sha1 != $this->getParam('sha1')) {
            throw new \Exception('Webhook validation failed. bad checksum: ' . $sha1);
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

//    public static function escapeHtml($message){
//        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
//    }
}
