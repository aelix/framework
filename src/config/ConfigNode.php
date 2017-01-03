<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\config;


use aelix\framework\Aelix;

class ConfigNode
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * ConfigNode constructor.
     * @param int $id
     * @param string $name
     * @param mixed $value
     */
    public function __construct($config, $id, $name, $value)
    {
        $this->config = $config;
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return ConfigNode
     */
    public function setValue($value): self
    {
        $this->value = $value;

        $stmt = Aelix::db()->prepare('UPDATE `' . $this->config->getTableName() . '` SET `value` = :value WHERE `id` = :id');
        $stmt->execute([
            ':id' => $this->id,
            ':value' => serialize($this->value)
        ]);

        return $this;
    }

}