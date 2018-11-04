<?php
namespace Application;

use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\Client\OAuth;


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
    }

    public function run(array $data = null)
    {
        $this->bootstrap();
        $this->dispatch($data['query']);
    }

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
            $shopModel = new \Application\Model\Shop();
            $shopModel->updateTokens($tokens['refresh_token'], $tokens['access_token'], $expirationDate, $shopId);
        } catch (\PDOException $ex) {
            throw new \Exception('Database error', 0, $ex);
        }

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
        return new $namespace($this->_calledController . DIRECTORY_SEPARATOR . ucfirst($this->_calledAction), $params);
    }
}
