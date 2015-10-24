<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\database\driver;


use aelix\framework\database\Database;
use PDO;
use PDOException;

class MySQLDatabase extends Database
{

    /**
     * @param string $host SQL server's hostname/IP or file name
     * @param string $username username for login
     * @param string $password password for login
     * @param string $database database to use
     * @param int $port port number if necessary
     *
     * @return \PDO
     */
    protected function connect($host = '', $username = '', $password = '', $database = '', $port = 0)
    {
        // Set default port
        if ($port == 0) {
            $port = 3306;
        }

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=utf8';

        $pdo = null;
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // TODO: aelix framework-own database exceptions?
            throw $e;
        }

        return $pdo;
    }

    /**
     * Is this database driver supported on this platform?
     * @return bool
     */
    public static function isSupported()
    {
        return (extension_loaded('PDO') && extension_loaded('pdo_mysql'));
    }

    /**
     * Get name of database driver currently in use
     * @return string
     */
    public function getDriverName()
    {
        return 'MySQL';
    }
}