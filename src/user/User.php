<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\user;


use aelix\framework\Aelix;
use aelix\framework\util\USecurity;

class User
{
    /**
     * Cache for constructors
     * @var User[]
     */
    protected static $userByID;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $fullname;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var UserData[]
     */
    protected $data;

    /**
     * @var string
     */
    protected $passwordHash;

    /**
     * User constructor.
     * @param array $row
     */
    protected function __construct(array $row)
    {
        $this->id = $row['id'];
        $this->username = $row['username'];
        $this->email = $row['email'];
        $this->fullname = $row['fullname'];
        $this->passwordHash = $row['passwordHash'];

        $this->loadUserData();
    }

    /**
     * loads all user data from DB
     */
    protected function loadUserData()
    {
        $stmt = Aelix::getDB()->prepare(
            'SELECT
                `user_data`.`id` AS `dataID`,
                `user_data`.`value`,
                `user_data_field`.`id` AS `fieldID`,
                `user_data_field`.`fieldName`
            FROM `user_data`
                INNER JOIN `user_data_field`
                ON `user_data`.`fieldID` = `user_data_field`.`id`
            WHERE
                `user_data`.`userID` = :id');

        $stmt->execute([
            ':id' => $this->id
        ]);

        foreach ($stmt->fetchAllArray() as $row) {
            // UserDataField class handles caching
            $field = UserDataField::getFieldByRow($row['fieldID'], $row['fieldName']);
            $this->data[$row['fieldName']] = new UserData($this, $field, $row['dataID'], $row['value']);
        }
    }

    /**
     * @param string $fieldName
     * @return mixed|null value, null if not exists
     */
    public function getData($fieldName)
    {
        if (isset($this->data[$fieldName]) && $this->data[$fieldName] instanceof UserData) {
            return $this->data[$fieldName]->getValue();
        } else {
            return null;
        }
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     */
    public function setData($fieldName, $value)
    {
        if (isset($this->data[$fieldName])) {
            $this->data[$fieldName]->setValue($value);
            return;
        }

        // check if we need a new field
        $field = UserDataField::createField($fieldName);
        $this->data[$field->getName()] = UserData::create($field, $this, $value);
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * check password
     * @param string $password clear text password
     * @return bool
     */
    public function checkPassword($password)
    {
        return USecurity::checkPassword($password, $this->passwordHash);
    }

    /**
     * check password security
     * @see USecurity::checkPasswordSecurity()
     * @return bool
     */
    public function checkPasswordSecurity()
    {
        return USecurity::checkPasswordSecurity($this->passwordHash);
    }

    /**
     * @param string $password clear text password
     * @param int $hashCost
     */
    public function setPassword($password, $hashCost = USecurity::HASHING_COST)
    {
        $hash = USecurity::encryptPassword($password, $hashCost);

        Aelix::getDB()->prepare('UPDATE `user` SET `passwordHash` = :hash WHERE `id` = :id')
            ->execute([
                ':hash' => $hash,
                ':id' => $this->id
            ]);
    }

    /**
     * @param int $userID
     * @return User
     * @throws \InvalidArgumentException
     */
    public static function getByID($userID)
    {
        if (isset(self::$userByID[$userID]) && self::$userByID[$userID] instanceof User) {
            return self::$userByID[$userID];
        } else {
            $stmt = Aelix::getDB()->prepare('SELECT * FROM `user` WHERE `id` = :userID')
                ->execute([
                    ':userID' => $userID
                ]);

            if ($stmt->rowCount() != 1) {
                throw new \InvalidArgumentException('User ID ' . $userID . ' does not exist.');
            }

            $user = new User($stmt->fetchArray());
            self::$userByID[$user->getID()] = $user;
            return $user;
        }
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $fullname
     * @param string $password clear text
     * @param int $hashCost
     * @return User
     */
    public static function create($username, $email, $fullname, $password, $hashCost = USecurity::HASHING_COST)
    {
        $passwordHash = USecurity::encryptPassword($password, $hashCost);

        Aelix::getDB()->prepare('INSERT INTO `user` SET
            `username` = :username,
            `email` = :email,
            `passwordHash` = :passwordHash,
            `fullname` = :fullname')
            ->execute([
                ':username' => $username,
                ':email' => $email,
                ':passwordHash' => $passwordHash,
                ':fullname' => $fullname
            ]);

        $userID = Aelix::getDB()->getPDO()->lastInsertId('user');

        $user = new User([
            'id' => $userID,
            'username' => $username,
            'email' => $email,
            'passwordHash' => $passwordHash,
            'fullname' => $fullname
        ]);

        self::$userByID[$userID] = $user;
        return $user;
    }
}