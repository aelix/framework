<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

// define path constants
define('DIR_SRC', dirname(__FILE__) . DS);
define('DIR_ROOT', dirname(DIR_SRC) . DS);

define('DIR_CACHE', DIR_ROOT . 'cache' . DS);

// debug mode
define('AELIX_DEBUG', true);

// misc constants
define('NL', PHP_EOL);

// UTF-8
ini_set('default_charset', 'UTF-8');
ini_set('php.input_encoding', 'UTF-8');
ini_set('php.internal_encoding', 'UTF-8');
ini_set('php.output_encoding', 'UTF-8');

define('STARTTIME', microtime(true));

require_once DIR_SRC . 'Aelix.php';
new aelix\framework\Aelix();
