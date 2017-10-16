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

    protected $_oConfig = null;
    protected $_env;

    /**
     * Application constructor.
     * Initializes twig
     */
    public function __construct() {
        $this->loadConfig('config.json');
        $aParamEnvList = $this->_oConfig['environments'];
        $sParamActive = $aParamEnvList['active'];
        $env = $aParamEnvList[$sParamActive];
        $this->_loader = new Twig_Loader_Filesystem($this->path(self::PATH_TEMPLATES));
        $this->_twig = new Twig_Environment($this->_loader, array(
            'cache' => $env['cache'] ? $this->path(self::PATH_CACHE) : false,
            'debug' => $env['debug'],
            'auto_reload' => true
        ));
    }

    /**
     * loads a config file (json format);
     * and loads it in oConfig protected property
     * @param string $sFile
     * @throws Exception
     */
    public function loadConfig($sFile = 'config.json') {
        if (file_exists($sFile)) {
            $this->_oConfig = json_decode(file_get_contents($sFile), true);
            if (!$this->_oConfig) {
                throw new Exception('invalid configuration file');
            }
        } else {
            throw new Exception('config file not found');
        }
    }

    /**
     * returns the default values for a parameters
     * @param $sParam string
     * @return string
     */
    public function defaultValue($sParam) {
        if (array_key_exists($sParam, $this->_oConfig['defaults'])) {
            return $this->_oConfig['defaults'][$sParam];
        } else {
            return '';
        }
    }


    /**
     * Retuns the value of the specified GET parameter
     * @param $sParam string parameter name
     * @param $sDefault string default value for this parameter
     * @return string value of the parameter, false if not found
     */
    public function param($sParam, $sDefault = '') {
        if (array_key_exists($sParam, $_GET)) {
            return $_GET[$sParam];
        } else {
            return $sDefault;
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
    public function template($sTemplate, array $data = array())  {
        $sFileName = $this->path(self::PATH_TEMPLATES, $sTemplate . '.html.twig');
        if (file_exists($sFileName)) {
            print $this->_twig->render($sTemplate . '.html.twig', $data);
        } else {
            print "error : file not found : $sTemplate";
        }
    }

    /**
     * Runs the application main function
     * Parses parameter "p"
     * Loads the appropriate template and displays it
     */
    public function run() {
        try {
            $sPage = strtr(
                $this->param('p', $this->defaultValue('page')),
                '.', '/'
            );
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

try {
    $app = new Application();
    $app->run();
} catch (Exception $e) {
    header('Content-type: text/plain');
    print "Error 500\nAn exception occured : ";
    print $e->getMessage();
}

