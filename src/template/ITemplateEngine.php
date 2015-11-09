<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\template;


interface ITemplateEngine
{
    /**
     * initiate template engine.
     * @param array $directories search directories for template files.
     * @param bool|true $caching
     */
    public function __construct(array $directories, $caching = true);

    /**
     * add a directory to template search dirs
     * @param string $directory
     * @param bool|true $primary
     */
    public function addDir($directory, $primary = true);

    /**
     * @param array $variables
     */
    public function assign($variables);

    /**
     * display the template
     * @param string $template template name
     */
    public function display($template);

    /**
     * parse the template and return it as string
     * @param string $template template name
     * @return string
     */
    public function parse($template);

    /**
     * is this template engine supported on this platform?
     * @return bool
     */
    public function isSupported();
}