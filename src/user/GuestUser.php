<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\user;


use aelix\framework\util\USecurity;

class GuestUser extends User
{
    const GUEST_USER_ID = -1;


    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'id' => self::GUEST_USER_ID,
            'username' => 'guest',
            'fullname' => 'Guest', // TODO: locale
            'email' => '',
            'passwordHash' => ''
        ]);
    }

    protected function loadUserData()
    {
        return;
    }

    /**
     * @param string $fieldName
     * @return mixed|null value, null if not exists
     */
    public function getData($fieldName)
    {
        return null;
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     */
    public function setData($fieldName, $value)
    {
        return;
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

    public function checkPassword($password)
    {
        return false;
    }

    public function checkPasswordSecurity()
    {
        return true;
    }

    public function setPassword($password, $hashCost = USecurity::HASHING_COST)
    {
        return;
    }
}