<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\config;

use aelix\framework\Aelix;
use aelix\framework\exception\CoreException;

/**
 * Database configuration
* @package aelix\framework
*/
class Config
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var ConfigNode[]
     */
    protected $nodes;

    /**
     * Config constructor.
     * @param $tableName
     * @throws CoreException
     */
    public function __construct($tableName)
    {
        if(!preg_match('/[a-zA-Z0-9_-]+/', $tableName)) {
            throw new CoreException('Unacceptable config table name ' . $tableName);
        }

        $this->tableName = $tableName;
        $this->updateAll();
    }

    public function updateAll()
    {
        $stmt = Aelix::getDB()->query('SELECT * FROM `' . $this->tableName . '`');

        if($stmt->rowCount() > 0) {
            foreach ($stmt->fetchAllArray() as $line) {
                $this->nodes[$line['name']] = new ConfigNode($this, $line['id'], $line['name'], unserialize($line['value']));
            }
        }
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param $name
     * @return ConfigNode
     * @throws ConfigNodeNotFoundException
     */
    public function getNode($name)
    {
        if(!isset($this->nodes[$name])) {
            throw new ConfigNodeNotFoundException('Config node ' . $name . ' not found');
        }

        return $this->nodes[$name];
    }


    /**
     * Update existing node or create a new
     * @param $name
     * @param $value
     */
    public function updateNode($name, $value)
    {
        if(isset($this->nodes[$name])) {
            $this->nodes[$name]->setValue($value);
        } else {
            $this->addNode($name, $value);
        }
    }

    /**
     * @param $name
     * @param $value
     * @return ConfigNode
     * @throws ConfigNodeAlreadyExistsException
     */
    public function addNode($name, $value)
    {
        if(isset($this->nodes[$name])) {
            throw new ConfigNodeAlreadyExistsException('Config node ' . $name . ' already exists');
        }

        $stmt = Aelix::getDB()->prepare('INSERT INTO `' . $this->tableName . '` SET `name` = :name, `value` = :value');
        $stmt->execute([
            ':name' => $name,
            ':value' => serialize($value)
        ]);

        $node = new ConfigNode($this, Aelix::getDB()->getPDO()->lastInsertId($this->tableName), $name, $value);

        $this->nodes[$name] = $node;
        return $node;
    }
}