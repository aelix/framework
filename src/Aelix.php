<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework;

use aelix\framework\database\DatabaseFactory;

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

    public final function __construct()
    {

        // init autoloader
        require_once DIR_SRC . 'Autoload.php';
        self::$autoloader = new Autoloader();
        self::$autoloader->addNamespace('aelix\framework', DIR_SRC);
        self::$autoloader->register();

        // load database config
        if (!is_file(DIR_SRC . 'config.php')) {
            // TODO: proper exceptions
            exit('Config file src/config.php not found.');
        }
        $config = require_once DIR_SRC . 'config.example.php';

        // init DB
        self::$db = DatabaseFactory::initDatabase(
            $config['database.driver'],
            $config['database.host'],
            $config['database.user'],
            $config['database.password'],
            $config['database.name'],
            $config['database.port']
        );

        // unset $config for security reasons
        unset($config);

    }

    public final static function getAutoloader()
    {
        return self::$autoloader;
    }

    public final static function getDB()
    {
        return self::$db;
    }

}