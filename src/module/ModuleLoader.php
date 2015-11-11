<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\module;


use aelix\framework\Aelix;

class ModuleLoader
{
    const MODULE_FILE = 'module.php';

    /**
     * @var Module[]
     */
    protected $modules;

    /**
     * @var string
     */
    protected $modulesDir;

    public function __construct($modulesDir)
    {
        $this->modulesDir = $modulesDir;

        foreach (scandir($modulesDir) as $module) {
            if ($module{0} != '.' && is_file($modulesDir . $module . DS . self::MODULE_FILE)) {

                require_once $modulesDir . $module . DS . self::MODULE_FILE;
                $className = preg_replace('/\./', '_', $module);

                $moduleObj = new $className;

                if ($moduleObj instanceof Module) {
                    $this->modules[$module] = $moduleObj;
                }
                unset($moduleObj);
            }
        }
    }

    /**
     * register module namespaces to autoloader
     */
    public function registerNamespaces()
    {
        foreach ($this->modules as $module) {
            chdir($this->modulesDir . $module->getName());

            foreach ($module->getNamespaces() as $namespace => $dir) {
                if (is_array($dir)) {
                    foreach ($dir as $curDir) {
                        Aelix::getAutoloader()->addNamespace($namespace, realpath($curDir));
                    }
                } elseif (is_string($dir)) {
                    Aelix::getAutoloader()->addNamespace($namespace, realpath($dir));
                }
            }
        }

        chdir(DIR_START);
    }

    /**
     * load all modules
     */
    public function load()
    {
        foreach ($this->modules as $module) {
            $module->onLoad();
        }
    }

    /**
     * unload all modules
     */
    public function unload()
    {
        foreach ($this->modules as $module) {
            $module->onUnload();
        }
    }
}