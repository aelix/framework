<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

define('DS', DIRECTORY_SEPARATOR);
define('DIR_PUBLIC', dirname(__FILE__) . DS);
define('DIR_START', dirname(__FILE__) . DS); // from where we started the execution

require '..' . DS . 'src' . DS . 'bootstrap.php';
