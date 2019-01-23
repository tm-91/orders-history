<?php
namespace Application\View;

class View
{
	protected $_params = [];
    protected $_viewDirectory = false;

    /**
     * @var bool|\Psr\Log\LoggerInterface
     */
    protected $_logger = false;

    protected $translations = false;

	public function __construct($viewDirectory, \Logger $logger){
        $this->_viewDirectory = $viewDirectory;
        $logger->_addScope('View');
        $this->_logger = $logger;
	}

	public function setTranslations(array $translations) {
	    $this->translations = $translations;
    }

	public function renderHeader(){
        require __DIR__ . DIRECTORY_SEPARATOR . 'header.php';
    }

	public function renderFooter(){
        require __DIR__ . DIRECTORY_SEPARATOR . 'footer.php';
    }

    public function render(array $params = null){
        $this->_logger->debug('rendering ' . $this->_viewDirectory);
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $this->_viewDirectory . '.php';
        if (file_exists($filePath)) {
            if ($params) {
                extract($params);
            }
            require $filePath;
        } else {
            throw new \Exception('Did not found view file: ' . $filePath);
        }
    }

    public static function echoRec($array, array $translations = null) {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if ($translations && isset($translations[$key])){
                    $key = $translations[$key];
                }
                if (!is_numeric($key)){
                    echo '<div class="row"><div class="key header"><h3>' . $key . '</h3></div></div>';
                }
                static::echoRec($val, $translations);
            } else {
                if ($translations && isset($translations[$key])){
                    $key = $translations[$key];
                }
                echo '<div class="row"><div class="key">' . $key . ': </div><div class="val"> ' . $val . '</div></div>';
            }
        }
    }

	public function logger(){
		return $this->_logger;
	}
}