<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\template;


use aelix\framework\exception\CoreException;

class TemplateEngineFactory
{

    /**
     * @param string $engine template engine name
     * @param array $directories template file search directories
     * @param bool|true $caching caching enabled?
     * @return ITemplateEngine
     * @throws CoreException
     */
    public static function initTemplateEngine(string $engine, array $directories = [], $caching = true): ITemplateEngine
    {
        $engineClass = '\aelix\framework\template\engine\\' . $engine . 'TemplateEngine';

        // check if driver exists
        if (empty($engine) || !class_exists($engineClass, true)) {
            throw new CoreException('Specified template engine could not be found.', 0,
                'The configured template engine ' . $engine . ' could not be found. Please check spelling or install the missing module.');
        }

        // check if driver class is legit
        if (!is_subclass_of($engineClass, '\aelix\framework\template\ITemplateEngine', true)) {
            throw new CoreException('Template engine corrupted', 0,
                'The template engine ' . $engine . ' could not be used. It seems to be corrupted.');
        }

        // check if driver is supported
        if (!call_user_func([$engineClass, 'isSupported'])) {
            throw new CoreException('Template engine not supported', 0,
                'The template engine ' . $engine . ' is not supported on this platform. Please check the documentation.');
        }

        // do the magic
        return new $engineClass($directories, $caching);
    }

}