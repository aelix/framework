<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\user;


use aelix\framework\Aelix;

class UserData
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserDataField
     */
    protected $field;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * UserData constructor.
     * @param User $user
     * @param UserDataField $field
     * @param int $id
     * @param string $value serialized
     */
    public function __construct(User $user, UserDataField $field, $id, $value)
    {
        $this->user = $user;
        $this->field = $field;
        $this->id = $id;
        $this->value = unserialize($value);
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        Aelix::db()->prepare('UPDATE `user_data` SET `value` = :value WHERE `id` = :id')
            ->execute([
                ':value' => serialize($this->value),
                ':id' => $this->id
            ]);
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return UserDataField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param UserDataField $field
     * @param User $user
     * @param mixed $value
     * @return UserData
     * @throws \InvalidArgumentException
     */
    public static function create(UserDataField $field, User $user, $value)
    {
        // check if field is already set for user
        if ($user->getData($field->getName()) !== null) {
            throw new \InvalidArgumentException('User\'s data field ' . $field->getName() . ' already set.');
        }

        $value = serialize($value);

        Aelix::db()->prepare('INSERT INTO `user_data`
            SET `userID` = :userID, `fieldID` = :fieldID, `value` = :value')
            ->execute([
                ':userID' => $user->getID(),
                ':fieldID' => $field->getID(),
                ':value' => $value
                /**
                 *
                 */
            ]);

        $dataID = Aelix::db()->getPDO()->lastInsertId('user_data');

        return new UserData($user, $field, $dataID, $value);
    }

}