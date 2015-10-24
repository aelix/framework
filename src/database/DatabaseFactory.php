<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\database;


class DatabaseFactory
{

    public static function initDatabase($driver, $host, $user, $password, $database, $port)
    {

        $driverClass = '\aelix\framework\database\driver\\' . $driver . 'Database';

        // check if driver exists
        if (empty($driver) || !class_exists($driverClass, true)) {
            // TODO: proper exceptions
            exit('Database driver not found.');
        }

        // check if driver class is legit
        if (!is_subclass_of($driverClass, '\aelix\framework\database\Database', true)) {
            exit('Database driver is corrupted.');
        }

        // check if driver is supported
        if (!call_user_func([$driverClass, 'isSupported'])) {
            exit('Database driver is not supported.');
        }

        // do the magic
        return new $driverClass($host, $user, $password, $database, $port);

    }

}