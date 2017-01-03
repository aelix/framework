<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\util;


class UString
{
    /**
     * Convert special HTML chars
     * @param  string $string
     * @return string
     */
    public static function encodeHTML(string $string): string
    {
        return @htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8');
    }
}