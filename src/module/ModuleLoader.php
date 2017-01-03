<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

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

    /**
     * ModuleLoader constructor.
     * @param string $modulesDir
     */
    public function __construct(string $modulesDir)
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
     * @return ModuleLoader
     */
    public function registerNamespaces(): self
    {
        foreach ($this->modules as $module) {
            chdir($this->modulesDir . $module->getName());

            foreach ($module->getNamespaces() as $namespace => $dir) {
                if (is_array($dir)) {
                    foreach ($dir as $curDir) {
                        Aelix::autoloader()->addNamespace($namespace, realpath($curDir));
                    }
                } elseif (is_string($dir)) {
                    Aelix::autoloader()->addNamespace($namespace, realpath($dir));
                }
            }
        }

        chdir(DIR_START);

        return $this;
    }

    /**
     * load all modules
     * @return ModuleLoader
     */
    public function load(): self
    {
        foreach ($this->modules as $module) {
            $module->onLoad();
        }

        return $this;
    }

    /**
     * unload all modules
     * @return ModuleLoader
     */
    public function unload(): self
    {
        foreach ($this->modules as $module) {
            $module->onUnload();
        }

        return $this;
    }
}