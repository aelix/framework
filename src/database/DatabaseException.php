<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\database;


use aelix\framework\exception\CoreException;
use aelix\framework\util\UString;

class DatabaseException extends CoreException
{

    /**
     * @var int
     */
    protected $sqlErrorCode;

    /**
     * @var string
     */
    protected $sqlErrorDescription;

    /**
     * @var string
     */
    protected $sqlVersion = '';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $dbDriverName;

    /**
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * @param string $message
     * @param Database $db
     * @param DatabaseStatement|null $statement
     */
    public function __construct($message, Database $db, DatabaseStatement $statement = null)
    {
        $this->db = $db;
        $this->statement = $statement;
        $this->dbDriverName = $db->getDriverName();

        // prefer errors by the statement
        if ($this->statement !== null && $this->statement->errorCode()) {
            $this->sqlErrorCode = $this->statement->errorCode();
            $this->sqlErrorDescription = $this->statement->errorInfo()[2];
        } else {
            $this->sqlErrorCode = $this->db->getPDO()->errorCode();
            $this->sqlErrorDescription = $this->db->getPDO()->errorInfo()[2];
        }

        parent::__construct($message, intval($this->sqlErrorCode));
    }

    /**
     * @return int
     */
    public function getSqlErrorCode()
    {
        return $this->sqlErrorCode;
    }

    /**
     * @return string
     */
    public function getSqlVersion()
    {
        if ($this->sqlVersion === '') {
            try {
                $this->sqlVersion = $this->db->getPDO()->getAttribute(\PDO::ATTR_SERVER_VERSION);
            } catch (\PDOException $e) {
                return 'unknown';
            }
        }
        return $this->sqlVersion;
    }

    /**
     * @return string
     */
    public function getDbDriverName()
    {
        return $this->dbDriverName;
    }

    public function show()
    {
        // debugDumpParams() can only output, need to catch that
        ob_start();
        $this->statement->debugDumpParams();
        $debugDumpParams = ob_get_clean();

        // Put it into the information var
        $this->information .= '<strong>SQL type:</strong> ' . UString::encodeHTML($this->dbDriverName) . '<br>' . NL;
        $this->information .= '<strong>SQL error number:</strong> ' . UString::encodeHTML($this->sqlErrorCode) . '<br>' . NL;
        $this->information .= '<strong>SQL error message:</strong> ' . UString::encodeHTML($this->sqlErrorDescription) . '<br>' . NL;
        $this->information .= '<strong>SQL version:</strong> ' . UString::encodeHTML($this->getSqlVersion()) . '<br>' . NL;

        // Do we have some additional query stuff?
        if ($this->statement !== null) {
            $this->information .= '<strong>SQL query:</strong> ' . UString::encodeHTML($debugDumpParams) . '<br>' . NL;
        }
        parent::show();
    }

}