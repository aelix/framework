<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\module;


abstract class Module
{
    /**
     * module name
     * @var string
     */
    private $name = '';

    /**
     * get module name
     * @return string
     */
    public final function getName()
    {
        if ($this->name === '') {
            $this->name = preg_replace('/\_/', '.', get_class($this));
        }

        return $this->name;
    }

    /**
     * function called on load of the module
     */
    public abstract function onLoad();

    /**
     * method called on unloading the module
     */
    public abstract function onUnload();

    /**
     * @return string
     */
    public final function getDirectory()
    {
        $info = new \ReflectionClass($this);
        return dirname($info->getFileName()) . DS;
    }

    /**
     * get array of namespaces to register in autoloader
     * namespace => directory OR
     * namespace => array of directories
     *
     * @return array
     */
    public abstract function getNamespaces();

}