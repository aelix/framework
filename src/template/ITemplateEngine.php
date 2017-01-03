<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

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
     * is this template engine supported on this platform?
     * @return bool
     */
    public static function isSupported(): bool;

    /**
     * add a directory to template search dirs
     * @param string $directory
     * @param bool|true $primary
     * @return ITemplateEngine
     */
    public function addDir($directory, $primary = true): ITemplateEngine;

    /**
     * assign template variables
     * if objects of type ITemplatable are used in the array, these will be converted to their getTemplateArray()
     * value
     * @param array|ITemplatable[] $variables
     * @param bool $merge
     * @return ITemplateEngine
     */
    public function assign(array $variables, $merge = false): ITemplateEngine;

    /**
     * display the template
     * @param string $template template name
     * @param bool $defaultExtension if to use the default template engine's file extension for template files
     * @return ITemplateEngine
     */
    public function display($template, $defaultExtension = false): ITemplateEngine;

    /**
     * parse the template and return it as string
     * @param string $template template name
     * @param bool $defaultExtension if to use the default template engine's file extension for template files
     * @return string
     */
    public function parse($template, $defaultExtension = false): string;
}