<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\config;

use aelix\framework\Aelix;
use aelix\framework\exception\CoreException;
use aelix\framework\template\ITemplatable;

/**
 * Database configuration
 * @package aelix\framework
 */
class Config implements ITemplatable
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
     * @param string $tableName
     * @throws CoreException
     */
    public function __construct(string $tableName)
    {
        if (!preg_match('/[a-zA-Z0-9_-]+/', $tableName)) {
            throw new CoreException('Unacceptable config table name ' . $tableName);
        }

        $this->tableName = $tableName;
        $this->updateAll();
    }

    /**
     * reads all Config nodes from the DB
     * @return Config
     */
    public function updateAll(): self
    {
        $stmt = Aelix::db()->query('SELECT * FROM `' . $this->tableName . '`');

        if ($stmt->rowCount() > 0) {
            foreach ($stmt->fetchAllArray() as $line) {
                $this->nodes[$line['name']] = new ConfigNode($this, $line['id'], $line['name'],
                    unserialize($line['value']));
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws ConfigNodeNotFoundException
     */
    public function get(string $name)
    {
        return $this->getNode($name)->getValue();
    }

    /**
     * @param string $name
     * @return ConfigNode
     * @throws ConfigNodeNotFoundException
     */
    public function getNode(string $name): ConfigNode
    {
        if (!isset($this->nodes[$name])) {
            throw new ConfigNodeNotFoundException('Config node ' . $name . ' not found');
        }

        return $this->nodes[$name];
    }

    /**
     * Update existing node or create a new
     * @param string $name
     * @param mixed $value
     * @return Config
     */
    public function update(string $name, $value): self
    {
        if (isset($this->nodes[$name])) {
            $this->nodes[$name]->setValue($value);
        } else {
            $this->add($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Config
     * @throws ConfigNodeAlreadyExistsException
     */
    public function add(string $name, $value): self
    {
        if (isset($this->nodes[$name])) {
            throw new ConfigNodeAlreadyExistsException('Config node ' . $name . ' already exists');
        }

        $stmt = Aelix::db()->prepare('INSERT INTO `' . $this->tableName . '` SET `name` = :name, `value` = :value');
        $stmt->execute([
            ':name' => $name,
            ':value' => serialize($value)
        ]);

        $node = new ConfigNode($this, Aelix::db()->getPDO()->lastInsertId($this->tableName), $name, $value);

        $this->nodes[$name] = $node;
        return $this;
    }

    /**
     * get an associative array suitable for assigning to template variables
     * @return array
     */
    public function getTemplateArray(): array
    {
        $temp = [];

        foreach ($this->nodes as $node) {
            $temp[$node->getName()] = $node->getValue();
        }

        return $temp;
    }
}