<?php
namespace Webhooks;

use \Core\Model\Shop;
/**
 * Class App
 */
class App extends \Core\AbstractApp
{
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

        $this->_params = $this->getWebhookHeaders();
        $renamed = [
            'id'        => $this->getParam('X-Webhook-Id'),
            'name'      => $this->getParam('X-Webhook-Name'),
            'shop'      => $this->getParam('X-Shop-Domain'),
            'license'   => $this->getParam('X-Shop-License'),
            'sha1'      => $this->getParam('X-Webhook-Sha1')
        ];
        $this->_params = $renamed;

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
}
