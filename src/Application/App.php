<?php
namespace Application;

use \Core\Model\Shop;

class App extends \Core\AbstractApp
{

    /**
     * @var null|\DreamCommerce\ShopAppstoreLib\Client
     */
    protected $_client = null;
    /**
     * @var string default locale
     */
    protected $_locale = 'pl_PL';

    /**
     * @var Shop
     */
    protected $_shop = false;

    /**
     * @var array url parameters storage
     */
    protected $_params = array();

    protected $_defaultController = 'Index';
    protected $_defaultAction = 'index';
    protected $_controllerNamespace = __NAMESPACE__ . '\Controller';
    protected $_viewNamespace = __NAMESPACE__ . '\View';
    protected $_modelNamespace = __NAMESPACE__ . '\Model';

    const MODULE_NAME = 'Application';

//    /**
//     * instantiate
//     * @param array $config
//     */
//    public function __construct()
//    {
////         parent::__construct();
//        if (empty($_GET['locale'])) {
//            die();
//        }
//        setlocale(LC_ALL, basename($_GET['locale']));
//    }

    /**
     * main application bootstrap
     * @throws \Exception
     */
    public function bootstrap()
    {
        parent::bootstrap();

        $this->_params = $_GET;

        // check request hash and variables
        $this->validateRequest();

        $this->_locale = basename($this->getParam('translations'));

        $this->_shop = Shop::getInstance(
            $this->getParam('shop'),
            new \Core\Model\Tables\Shops(),
            new \Core\Model\Tables\AccessTokens(),
            new \Core\Model\Tables\Queries()
        );
        // detect if shop is already installed
        if (!$this->_shop) {
            throw new \Exception('shop is not installed! license: ' . $this->getParam('shop'));
        }

        // refresh token
        if (strtotime($this->_shop->getToken()->expiresAt() - time() < 86400)) {
            $this->_shop->refreshToken(self::getConfig('appId'), self::getConfig('appSecret'));
        }

        // instantiate SDK client
        $this->_client = $this->_shop->instantiateSDKClient(self::getConfig('appId'), self::getConfig('appSecret'));
    }

    public function run(array $data = null)
    {
        $this->bootstrap();
        $this->dispatch($data['query']);
    }

    public function shop(){
        return $this->_shop;
    }

    public function getParam($param = null)
    {
        if (is_null($param)) {
            return $this->_params;
        }
        if (isset($this->_params[$param])) {
            return $this->_params[$param];
        } else {
            throw new \Exception('Parameter "' . $param . '" is not set');
        }
    }

    /**
     * get client resource
     * @throws \Exception
     * @return \DreamCommerce\ShopAppstoreLib\Client|null
     */
    public function getClient()
    {
        if ($this->_client === null) {
            throw new \Exception('Client is NOT instantiated');
        }

        return $this->_client;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * checks variables and hash
     * @throws \Exception
     */
    public function validateRequest()
    {
        // todo
        if (empty($_GET['locale'])) {
            die();
        }
        setlocale(LC_ALL, basename($_GET['locale']));




        if (empty($this->getParam('translations'))) {
            throw new \Exception('Invalid request');
        }

        $params = array(
            'place' => $this->getParam('place'),
            'shop' => $this->getParam('shop'),
            'timestamp' => $this->getParam('timestamp'),
        );

        ksort($params);
        $parameters = array();
        foreach ($params as $k => $v) {
            $parameters[] = $k . "=" . $v;
        }
        $p = join("&", $parameters);


        $hash = hash_hmac('sha512', $p, self::getConfig('appstoreSecret'));

        if ($hash != $this->getParam('hash')) {
            throw new \Exception('Invalid request');
        }
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

    public function getView(array $params = array()){
        $translations = require 'translations.php';
        $params['translations'] = $translations[$this->getLocale()];
        $namespace = '\\' . self::MODULE_NAME . '\\' . self::VIEW_NAMESPACE . '\\' . 'View';
        return new $namespace($this->_calledController . DIRECTORY_SEPARATOR . ucfirst($this->_calledAction), $params);
    }
}
