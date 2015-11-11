<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

/*
 * init aelix
 */
define('DS', DIRECTORY_SEPARATOR);
define('DIR_START', dirname(__FILE__) . DS); // from where we started the execution

// only init aelix (until database)
// we need the DB for phinx
define('AELIX_ONLY_INIT', true);

require 'src' . DS . 'bootstrap.php';

/*
 * aelix done, generate phinx config
 */

use aelix\framework\Aelix;

// return phinx config array
return [
    'paths' => [
        'migrations' => DIR_ROOT . 'migrations'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'aelix',
        'aelix' => [
            'name' => Aelix::getDB()->getDatabaseName(),
            'connection' => Aelix::getDB()->getPDO()
        ],
    ]
];
