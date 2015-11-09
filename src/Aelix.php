<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework;

use aelix\framework\config\Config;
use aelix\framework\database\DatabaseFactory;
use aelix\framework\exception\CoreException;
use aelix\framework\exception\IPrintableException;
use aelix\framework\module\ModuleLoader;

class Aelix
{

    /**
     * @var Autoloader
     */
    private static $autoloader;

    /**
     * @var database\Database
     */
    private static $db;

    /**
     * @var config\Config
     */
    private static $config;

    /**
     * @var module\ModuleLoader
     */
    private static $moduleLoader;
     * aelix constructor.
     */
    public final function __construct()
    {
        // register error and exception handler
        set_exception_handler(['\aelix\framework\Aelix', 'handleException']);
        set_error_handler(['\aelix\framework\Aelix', 'handleError'], E_ALL);

        // init autoloader
        require_once DIR_SRC . 'Autoload.php';
        self::$autoloader = new Autoloader();
        self::$autoloader->addNamespace('aelix\framework', DIR_SRC);
        self::$autoloader->register();

        // also use composer autoloader if necessary
        if (is_dir(DIR_ROOT . 'vendor') && is_file(DIR_ROOT . 'vendor' . DS . 'autoload.php')) {
            require_once DIR_ROOT . 'vendor' . DS . 'autoload.php';
        }

        // module loader
        self::$moduleLoader = new ModuleLoader(DIR_ROOT . 'modules' . DS);
        self::$moduleLoader->registerNamespaces();
        self::$moduleLoader->load();

        // load database config
        if (!is_file(DIR_ROOT . 'config.php')) {
            throw new CoreException('Could not find file ' . DIR_ROOT . 'config.php!', 0,
                'Unable to find or open the config file for aelix: ' . DIR_ROOT . 'config.php');
        }
        $config = require_once DIR_ROOT . 'config.php';

        // init DB
        self::$db = DatabaseFactory::initDatabase(
            $config['database.driver'],
            $config['database.host'],
            $config['database.user'],
            $config['database.password'],
            $config['database.database'],
            $config['database.port']
        );

        // unset $config for security reasons
        unset($config);

        // boot up configs
        self::$config = new Config('config');

    }

    /**
     * show exceptions
     * @param \Exception $e
     */
    public final static function handleException(\Exception $e)
    {
        if ($e instanceof IPrintableException) {
            $e->show();
            exit(1);
        }
        // repack
        self::handleException(new CoreException($e->getMessage(), $e->getCode(), '', $e));
    }

    /**
     * catches php errors and throws a CoreException instead
     * @param integer $errorNo
     * @param string $message
     * @param string $filename
     * @param integer $lineNo
     * @throws CoreException
     */
    public final static function handleError($errorNo, $message, $filename, $lineNo)
    {
        if (error_reporting() != 0) {
            $type = 'error';
            switch ($errorNo) {
                case 2:
                    $type = 'warning';
                    break;
                case 8:
                    $type = 'notice';
                    break;
            }
            throw new CoreException('PHP ' . $type . ' in file ' . $filename . ' (' . $lineNo . '): ' . $message, 0);
        }
    }

    /**
     * @return Autoloader
     */
    public final static function getAutoloader()
    {
        return self::$autoloader;
    }

    public final static function getConfig()
    {
        return self::$config;
    }

    /**
     * @return database\Database
     */
    public final static function getDB()
    {
        return self::$db;
    }

    /**
     * @return bool
     */
    public final static function isDebug()
    {
        return AELIX_DEBUG;
    }

}