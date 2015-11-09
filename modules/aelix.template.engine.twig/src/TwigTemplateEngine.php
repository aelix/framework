<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\template\engine;


use aelix\framework\Aelix;
use aelix\framework\template\ITemplateEngine;

class TwigTemplateEngine implements ITemplateEngine
{
    /**
     * template search directories
     * @var array
     */
    protected $directories;

    /**
     * caching enabled?
     * @var bool
     */
    protected $caching;

    /**
     * Twig file loaeder
     * @var \Twig_Loader_Filesystem
     */
    protected $twigLoader;

    /**
     * Twig environment
     * @var \Twig_Environment
     */
    protected $twigEnv;

    /**
     * template variables
     * @var array
     */
    protected $variables = [];

    /**
     * initiate template engine.
     * @param array $directories search directories for template files.
     * @param bool|true $caching
     */
    public function __construct(array $directories, $caching = true)
    {

        $this->directories = $directories;
        $this->caching = $caching;

        $this->twigLoader = new \Twig_Loader_Filesystem($directories);

        $options = [
            'charset' => 'utf-8',
            'cache' => ($caching ? DIR_CACHE . 'template' . DS : false),
            'auto_reload' => Aelix::isDebug(),
        ];

        $this->twigEnv = new \Twig_Environment($this->twigLoader, $options);

    }

    /**
     * add a directory to template search dirs
     * @param string $directory
     * @param bool|true $primary
     */
    public function addDir($directory, $primary = true)
    {
        if ($primary) {
            $this->twigLoader->prependPath($directory);
        } else {
            $this->twigLoader->addPath($directory);
        }

        $this->twigEnv->setLoader($this->twigLoader);
    }

    /**
     * @param array $variables
     */
    public function assign($variables)
    {
        $this->variables = $variables;
    }

    /**
     * is this template engine supported on this platform?
     * @return bool
     */
    public function isSupported()
    {
        // twig needs to be loaded via composer
        return class_exists('Twig_Environment', true);
    }

    /**
     * display the template
     * @param string $template template name
     */
    public function display($template)
    {
        $this->twigEnv->display($template, $this->variables);
    }

    /**
     * parse the template and return it as string
     * @param string $template template name
     * @return string
     */
    public function parse($template)
    {
        return $this->twigEnv->render($template, $this->variables);
    }
}