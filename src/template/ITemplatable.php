<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\template;


interface ITemplatable
{
    /**
     * get an associative array suitable for assigning to template variables
     * @return array
     */
    public function getTemplateArray();
}