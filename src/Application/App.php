<?php
namespace Application;

use \Core\Model\Shop;

class App extends \Core\AbstractApp
{
    /**
     * @var string default locale
     */
    protected $_locale = 'pl_PL';

    /**
     * @var Shop
     */
    protected $_shop = false;

    protected $_defaultController = 'Index';
    protected $_defaultAction = 'index';
    protected $_controllerNamespace = __NAMESPACE__ . '\Controller';
    protected $_viewNamespace = __NAMESPACE__ . '\View';
    protected $_modelNamespace = __NAMESPACE__ . '\Model';

    const MODULE_NAME = 'Application';

    /**
     * main application bootstrap
     * @throws \Exception
     */
    public function bootstrap()
    {
        parent::bootstrap();

        $this->_params = $_GET;

        $this->validateRequest();

        $this->_locale = basename($this->getParam('translations'));

        $this->_shop = Shop::getInstance($this->getParam('shop'));

        // refresh token
        if (strtotime($this->_shop->getToken()->expiresAt() - time() < 86400)) {
            $this->_shop->refreshToken();
        }
    }

    public function run(array $data = null)
    {
        $this->bootstrap();
        $this->dispatch($data['query']);
    }

    public function shop(){
        return $this->_shop;
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
        if ($locale = $this->getParam('locale', false)) {
            setlocale(LC_ALL, basename($locale));
        } else {
            throw new \Exception('Invalid request. "locale" parameter has not been provided');
        }

        if ($this->getParam('translations', false) === null) {
            throw new \Exception('Invalid request. "translations" parameter has not been provided');
        }

        $params = [
            'place' => $this->getParam('place'),
            'shop' => $this->getParam('shop'),
            'timestamp' => $this->getParam('timestamp'),
        ];

        ksort($params);
        $parameters = [];
        foreach ($params as $k => $v) {
            $parameters[] = $k . "=" . $v;
        }
        $hash = hash_hmac('sha512', join("&", $parameters), self::getConfig('appstoreSecret'));
        if ($hash != $this->getParam('hash')) {
            throw new \Exception('Invalid request. Wrong hash');
        }
    }

    public static function getUrl($url)
    {
        $params = array();
        parse_str($_SERVER['QUERY_STRING'], $params);
        $params['q'] = $url;
        $query = http_build_query($params);
        return $url . '?' . $query;
    }

    public function getView($viewPath = null){
        $trans = require 'translations.php';
        $namespace = '\\' . self::MODULE_NAME . '\\' . self::VIEW_NAMESPACE . '\\' . 'View';
        if ($viewPath == null) {
            $viewPath = $this->_calledController . DIRECTORY_SEPARATOR . ucfirst($this->_calledAction);
        }
        $view = new $namespace($viewPath, $this->logger());
        $view->setTranslations($trans[$this->getLocale()]);
        return $view;
    }

    public function handleException(\Exception $exception)
    {
        parent::handleException($exception);
        $this->getView('Error')->render();
    }
}
