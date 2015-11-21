<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\util;

/**
 * security utilities, mainly passwords
 * @package aelix\framework\util
 */
class USecurity
{
    const HASHING_COST = 12;
    const HASHING_ALGO = PASSWORD_BCRYPT;

    /**
     * hash a new password
     * @param string $password clear text password
     * @param int $hashingCost hashing cost. more = secure but slower
     * @return string|bool
     */
    public static function encryptPassword($password, $hashingCost = self::HASHING_COST)
    {
        return password_hash($password, self::HASHING_ALGO, ['cost' => $hashingCost]);
    }


    /**
     * checks if the hashed password still meets our security requirements. false if rehash is needed
     * @param string $hash current hash
     * @return bool
     */
    public static function checkPasswordSecurity($hash)
    {
        /*
         * PHP offers a password_needs_rehash() function, but it only checks if the values are the same.
         * e.g. having a different hashing cost for one password would result in rehashing and losing security
         * We check both relevant values ourselves and make sure we don't rehash unnecessarily
         */

        // check if cost is too little
        $info = password_get_info($hash);
        if ($info['options']['cost'] < self::HASHING_COST) {
            return true;
        }

        // check if algo has changed
        if ($info['algo'] != self::HASHING_ALGO) {
            return true;
        }

        return false;
    }

    public static function checkPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}