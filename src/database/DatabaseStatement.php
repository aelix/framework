<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\database;

/**
 * Class DatabaseStatement
 * @package aelix\framework\database
 *
 * @property-read string $queryString
 * @method bool bindColumn() bool bindColumn(mixed $column, mixed &$param, int $type, int $maxlen, mixed $driverdata)
 * @method bool bindParam() bool bindParam(mixed $parameter, mixed &$variable, int $data_type = PDO::PARAM_STR, int $length, mixed $driver_options)
 * @method bool bindValue() bool bindValue(mixed $parameter, mixed $value, int $data_type = PDO::PARAM_STR)
 * @method bool closeCursor()
 * @method int columnCount()
 * @method bool debugDumpParams()
 * @method string errorCode()
 * @method array errorInfo()
 * @method string fetchColumn() string fetchColumn(int $column_number = 0)
 * @method mixed fetchObject() mixed fetchObject(string $class_name = "stdClass", array $ctor_args)
 * @method mixed getAttribute() getAttribute(int $attribute)
 * @method array getColumnMeta() getColumnMeta(int $column)
 * @method bool nextRowset()
 * @method int rowCount()
 * @method bool setAttribute() setAttribute(int $attribute, mixed $value)
 * @method bool setFetchMode() setFetchMode(int $mode)
 */
class DatabaseStatement
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var \PDOStatement
     */
    protected $pdoStatement;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $parameters;

    /**
     * DatabaseStatement constructor.
     * @param Database $database
     * @param \PDOStatement $pdoStatement
     * @param string $query
     */
    public function __construct(Database $database, ?\PDOStatement $pdoStatement, string $query)
    {
        $this->database = $database;
        $this->pdoStatement = $pdoStatement;
        $this->query = $query;
    }

    /**
     * @param array $parameters
     * @return DatabaseStatement
     * @throws DatabaseException
     */
    public function execute($parameters = []): self
    {
        $this->parameters = $parameters;

        try {
            if (empty($parameters)) {
                $this->pdoStatement->execute();
            } else {
                $this->pdoStatement->execute($parameters);
            }
        } catch (\PDOException $e) {
            throw new DatabaseException('Could not execute statement: ' . $e->getMessage(), $this->database, $this);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->pdoStatement->{$name};
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws DatabaseException
     */
    public function __call(string $name, array $arguments)
    {
        try {
            return call_user_func_array([$this->pdoStatement, $name], $arguments);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), $this->database, $this);
        }
    }

    /**
     * @param int $type
     * @return array
     */
    public function fetchAllArray(int $type = \PDO::FETCH_ASSOC)
    {
        $return = [];
        while ($row = $this->fetchArray($type)) {
            $return[] = $row;
        }
        return $return;
    }

    /**
     * Fetch the next row
     * @see \PDOStatement::fetch()
     * @param int $type
     * @return array
     */
    public function fetchArray(int $type = \PDO::FETCH_ASSOC)
    {
        return $this->pdoStatement->fetch($type);
    }

    /**
     * @return \stdClass[]
     */
    public function fetchAllObject(): array
    {
        $return = [];
        while ($row = $this->fetchObject()) {
            $return[] = $row;
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Get id of last inserted row
     * @see \PDO::lastInsertId()
     * @param string $table
     * @return string
     */
    public function getLastInsertID(?string $table = null): string
    {
        return $this->database->getPDO()->lastInsertId($table);
    }

    /**
     * @return string
     */
    public function getErrorDescription(): string
    {
        if ($this->pdoStatement !== null) {
            if (isset($this->pdoStatement->errorInfo()[2])) {
                return $this->pdoStatement->errorInfo()[2];
            }
        }
        return '';
    }

}