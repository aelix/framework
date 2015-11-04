<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\exception;


interface IPrintableException
{
    /**
     * Print the exception
     * @return void
     */
    public function show();
}