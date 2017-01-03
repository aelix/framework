<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\user;


use aelix\framework\Aelix;
use aelix\framework\template\ITemplatable;
use aelix\framework\util\USecurity;

class User implements ITemplatable
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
     * @return User
     */
    protected function loadUserData(): self
    {
        $stmt = Aelix::db()->prepare(
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

        return $this;
    }

    /**
     * @param int $userID
     * @return User
     * @throws UserDoesntExistException
     */
    public static function getByID(int $userID): self
    {
        if (isset(self::$userByID[$userID]) && self::$userByID[$userID] instanceof User) {
            return self::$userByID[$userID];
        } else {
            $stmt = Aelix::db()->prepare('SELECT * FROM `user` WHERE `id` = :userID')
                ->execute([
                    ':userID' => $userID
                ]);

            if ($stmt->rowCount() != 1) {
                throw new UserDoesntExistException();
            }

            $user = new User($stmt->fetchArray());
            self::$userByID[$user->getID()] = $user;
            return $user;
        }
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $fullname
     * @param string $password clear text
     * @param int $hashCost
     * @return User
     */
    public static function create(
        string $username,
        string $email,
        string $fullname,
        string $password,
        int $hashCost = USecurity::HASHING_COST
    ): self {
        $passwordHash = USecurity::encryptPassword($password, $hashCost);

        Aelix::db()->prepare('INSERT INTO `user` SET
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

        $userID = Aelix::db()->getPDO()->lastInsertId('user');

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

    /**
     * @param string $fieldName
     * @return mixed|null value, null if not exists
     */
    public function getData(string $fieldName)
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
     * @return User
     */
    public function setData(string $fieldName, $value): self
    {
        if (isset($this->data[$fieldName])) {
            $this->data[$fieldName]->setValue($value);
            return $this;
        }

        // check if we need a new field
        $field = UserDataField::createField($fieldName);
        $this->data[$field->getName()] = UserData::create($field, $this, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * check password
     * @param string $password clear text password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return USecurity::checkPassword($password, $this->passwordHash);
    }

    /**
     * @param string $password clear text password
     * @param int $hashCost
     * @return User
     */
    public function setPassword(string $password, int $hashCost = USecurity::HASHING_COST): self
    {
        $hash = USecurity::encryptPassword($password, $hashCost);

        Aelix::db()->prepare('UPDATE `user` SET `passwordHash` = :hash WHERE `id` = :id')
            ->execute([
                ':hash' => $hash,
                ':id' => $this->id
            ]);

        return $this;
    }

    /**
     * get an associative array suitable for assigning to template variables
     * @return array
     */
    public function getTemplateArray(): array
    {
        $data = [];
        if ($this->data) {
            foreach ($this->data as $key => $value) {
                $data[$key] = $value->getValue();
            }
        }

        return [
            'id' => $this->id,
            'username' => $this->username,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'passwordNeedsRehash' => $this->checkPasswordSecurity(),
            'data' => $data
        ];
    }

    /**
     * check password security
     * @see USecurity::checkPasswordSecurity()
     * @return bool
     */
    public function checkPasswordSecurity(): bool
    {
        return USecurity::checkPasswordSecurity($this->passwordHash);
    }
}