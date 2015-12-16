<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\user;


use aelix\framework\Aelix;

class UserDataField
{
    /**
     * @var UserDataField[]
     */
    private static $fields;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * UserDataField constructor.
     * @param int $fieldID
     * @param string $fieldName
     */
    protected function __construct($fieldID, $fieldName)
    {
        $this->id = $fieldID;
        $this->name = $fieldName;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        Aelix::db()->prepare('UPDATE `user_data_field` SET `fieldName` = :name WHERE `id` = :id')
            ->execute([
                ':name' => $this->name,
                ':id' => $this->id
            ]);
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Try to find an existing data field
     * @param string $fieldName
     * @return UserDataField|null Null if not found
     */
    public static function getField($fieldName)
    {
        // check cache
        if (isset(self::$fields[$fieldName]) && self::$fields[$fieldName] instanceof UserDataField) {
            return self::$fields[$fieldName];
        } else {
            // search in DB
            $stmt = Aelix::db()->prepare('SELECT * FROM `user_data_field` WHERE `fieldName` = :fieldName')
                ->execute([
                    ':fieldName' => $fieldName
                ]);

            if ($stmt->rowCount() == 1) {
                // create from DB
                $row = $stmt->fetchArray();
                $obj = new UserDataField($row['id'], $row['fieldName']);
                self::$fields[$row['fieldName']] = $obj;
                return $obj;
            } else {
                // field not defined
                return null;
            }
        }
    }

    /**
     * creates a new field. if already exists, returns the existing
     * @param string $fieldName
     * @return UserDataField
     */
    public static function createField($fieldName)
    {
        // is already cached?
        if (isset(self::$fields[$fieldName]) && self::$fields[$fieldName] instanceof UserDataField) {
            return self::$fields[$fieldName];
        }

        // find in DB
        $rowCount = Aelix::db()->prepare('SELECT * FROM `user_data_field` WHERE `fieldName` = :fieldName')
            ->execute([
                ':fieldName' => $fieldName
            ])
            ->rowCount();

        // field exists, return it
        if ($rowCount > 0) {
            return self::getField($fieldName);
        }

        // create a new one
        Aelix::db()->prepare('INSERT INTO `user_data_field` SET `fieldName` = :fieldName')
            ->execute([
                ':fieldName' => $fieldName
            ]);
        $fieldID = Aelix::db()->getPDO()->lastInsertId('user_data_field');

        $obj = new UserDataField($fieldID, $fieldName);
        self::$fields[$fieldName] = $obj;
        return $obj;
    }

    public static function getFieldByRow($fieldID, $fieldName)
    {
        // check cache
        if (isset(self::$fields[$fieldName]) && self::$fields[$fieldName] instanceof UserDataField) {
            return self::$fields[$fieldName];
        } else {
            $obj = new UserDataField($fieldID, $fieldName);
            self::$fields[$fieldName] = $obj;
            return $obj;
        }
    }

}