<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\database;


use aelix\framework\exception\CoreException;

class DatabaseFactory
{

    /**
     * @param string $driver
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param int $port
     * @return Database
     * @throws CoreException
     */
    public static function initDatabase(
        string $driver,
        string $host,
        string $user,
        string $password,
        string $database,
        int $port
    ): Database {

        $driverClass = '\aelix\framework\database\driver\\' . $driver . 'Database';

        // check if driver exists
        if (empty($driver) || !class_exists($driverClass, true)) {
            throw new CoreException('Specified database driver could not be found.', 0,
                'The configured database driver ' . $driver . ' could not be found. Please check spelling or install the missing module.');
        }

        // check if driver class is legit
        if (!is_subclass_of($driverClass, '\aelix\framework\database\Database', true)) {
            throw new CoreException('Database driver corrupted', 0,
                'The database driver ' . $driver . ' could not be used. It seems to be corrupted.');
        }

        // check if driver is supported
        if (!call_user_func([$driverClass, 'isSupported'])) {
            throw new CoreException('Database driver not supported', 0,
                'The database driver ' . $driver . ' is not supported on this platform. Please check the documentation.');
        }

        // do the magic
        return new $driverClass($host, $user, $password, $database, $port);

    }

}