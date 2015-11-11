<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\database;

abstract class Database
{

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @param string $host SQL server's hostname/IP or file name
     * @param string $username username for login
     * @param string $password password for login
     * @param string $database database to use
     * @param int $port port number if necessary
     */
    public function __construct($host = '', $username = '', $password = '', $database = '', $port = 0)
    {
        $this->databaseName = $database;
        $this->pdo = $this->connect($host, $username, $password, $database, $port);
    }

    /**
     * @param string $host SQL server's hostname/IP or file name
     * @param string $username username for login
     * @param string $password password for login
     * @param string $database database to use
     * @param int $port port number if necessary
     *
     * @return \PDO
     */
    abstract protected function connect($host = '', $username = '', $password = '', $database = '', $port = 0);

    /**
     * Is this database driver supported on this platform?
     * @return bool
     */
    public static function isSupported()
    {
        return false;
    }

    /**
     * Get name of database driver currently in use
     * @return string
     */
    abstract public function getDriverName();

    /**
     * @return \PDO
     */
    public final function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Execute a query
     *
     * @param $query
     * @return DatabaseStatement
     * @throws DatabaseException
     */
    public function query($query)
    {
        try {
            $pdoStatement = $this->pdo->query($query);
            if ($pdoStatement instanceof \PDOStatement) {
                return new DatabaseStatement($this, $pdoStatement, $query);
            }
        } catch (\PDOException $e) {
            throw new DatabaseException('Could not execute query: ' . $e->getMessage(), $this);
        }

        return null;
    }

    /**
     * Prepare a statement
     *
     * @param $query
     * @return DatabaseStatement
     * @throws DatabaseException
     */
    public function prepare($query)
    {
        try {
            $pdoStatement = $this->pdo->prepare($query);
            if ($pdoStatement instanceof \PDOStatement) {
                return new DatabaseStatement($this, $pdoStatement, $query);
            }
        } catch (\PDOException $e) {
            throw new DatabaseException('Could not prepare statement: ' . $e->getMessage(), $this);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

}
