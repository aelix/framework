<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

use aelix\framework\module\Module;

class aelix_database_driver_mysql extends Module
{
    /**
     * function called on load of the module
     */
    public function onLoad(): void
    {

    }

    /**
     * method called on unloading the module
     */
    public function onUnload(): void
    {

    }


    /**
     * get array of namespaces to register in autoloader
     * namespace => directory OR
     * namespace => array of directories
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return [
            'aelix\\framework\\database\\driver' => 'src'
        ];
    }
}