<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

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

    /**
     * @param string $fieldName
     * @return mixed|null value, null if not exists
     */
    public function getData(string $fieldName)
    {
        return null;
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     * @return User
     */
    public function setData(string $fieldName, $value): User
    {
        return $this;
    }

    public function checkPassword(string $password): bool
    {
        return false;
    }

    public function checkPasswordSecurity(): bool
    {
        return true;
    }

    public function setPassword(string $password, int $hashCost = USecurity::HASHING_COST): User
    {
        return $this;
    }

    protected function loadUserData(): User
    {
        return $this;
    }
}