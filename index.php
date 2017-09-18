<?php
/**
 * Created by PhpStorm.
 * User: ralphy
 * Date: 15/09/17
 * Time: 23:07
 */
require_once 'vendor/autoload.php';

class Application {

    const PATH_TEMPLATES = 'templates';
    const PATH_CACHE = 'cache';

    protected $_loader;
    protected $_twig;

    static $CONFIG = array(
        'cache' => false,
        'debug' => true
    );

    /**
     * Application constructor.
     * Initializes twig
     */
    public function __construct() {
        $this->_loader = new Twig_Loader_Filesystem($this->path(self::PATH_TEMPLATES));
        $this->_twig = new Twig_Environment($this->_loader, array(
            'cache' => self::$CONFIG['cache'] ? $this->path(self::PATH_CACHE) : false
        ));
    }

    /**
     * Retuns the value of the specified GET parameter
     * @param $sParam string parameter name
     * @return string|boolean value of the parameter, false if not found
     */
    public function param($sParam) {
        if (array_key_exists($sParam, $_GET)) {
            return $_GET[$sParam];
        } else {
            return false;
        }
    }

    /**
     * completes path, making it absolute
     * @return string
     */
    public function path() {
        $aPaths = func_get_args();
        array_unshift($aPaths, __DIR__);
        $sPath = implode('/', $aPaths);
        $sPath = str_replace('//', '/', $sPath);
        return $sPath;
    }

    /**
     * loads a template and print it.
     * @param $sTemplate string template file name
     * @param $data array variables used by the template
     */
    public function template(string $sTemplate, array $data = array())  {
        $sFileName = $this->path(self::PATH_TEMPLATES, $sTemplate . '.html.twig');
        if (file_exists($sFileName)) {
            print $this->_twig->render($sTemplate . '.html.twig', $data);
        } else {
            print "error : file not found : $sTemplate";
        }
    }

    public function run() {
        try {
            $sPage = $this->param('p');
            if ($sPage === false) {
                $sPage = 'index';
            }
            $this->template($sPage);
        } catch (Twig_Error $e) {
            $aSource = explode("\n", $e->getSourceContext()->getCode());
            $iLine = $e->getTemplateLine();
            $iStart = max(0, $iLine - 3);
            $nLen = 5;
            $aContext = array_slice($aSource, $iStart, $nLen);
            $a = array(
                'file' => $e->getSourceContext()->getName(),
                'line' => $iLine,
                'tline' => $e->getTemplateLine(),
                'message' => $e->getMessage(),
                'source' => $aContext,
                'start' => $iStart + 1
            );
            $this->template('layout/error', $a);
        }
    }
}

$app = new Application();
$app->run();

