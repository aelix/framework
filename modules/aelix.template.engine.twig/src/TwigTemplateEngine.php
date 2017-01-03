<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\template\engine;


use aelix\framework\Aelix;
use aelix\framework\template\ITemplatable;
use aelix\framework\template\ITemplateEngine;

class TwigTemplateEngine implements ITemplateEngine
{

    const DEFAULT_EXTENSION = '.twig';

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
     * is this template engine supported on this platform?
     * @return bool
     */
    public static function isSupported(): bool
    {
        // twig needs to be loaded via composer
        return class_exists('Twig_Environment', true);
    }

    /**
     * add a directory to template search dirs
     * @param string $directory
     * @param bool|true $primary
     * @return ITemplateEngine
     */
    public function addDir($directory, $primary = true): ITemplateEngine
    {
        if ($primary) {
            $this->twigLoader->prependPath($directory);
        } else {
            $this->twigLoader->addPath($directory);
        }

        $this->twigEnv->setLoader($this->twigLoader);

        return $this;
    }

    /**
     * display the template
     * @param string $template template name
     * @param bool $defaultExtension if to use the default template engine's file extension for template files
     * @return ITemplateEngine
     */
    public function display($template, $defaultExtension = false): ITemplateEngine
    {
        $this->twigEnv->display($template . ($defaultExtension ? self::DEFAULT_EXTENSION : ''), $this->variables);
        return $this;
    }

    /**
     * parse the template and return it as string
     * @param string $template template name
     * @param bool $defaultExtension if to use the default template engine's file extension for template files
     * @return string
     */
    public function parse($template, $defaultExtension = false): string
    {
        return $this->twigEnv->render($template . ($defaultExtension ? self::DEFAULT_EXTENSION : ''), $this->variables);
    }

    /**
     * assign template variables
     * if objects of type ITemplatable are used in the array, these will be converted to their getTemplateArray()
     * value
     * @param array|ITemplatable[] $variables
     * @param bool $merge
     * @return ITemplateEngine
     */
    public function assign(array $variables, $merge = false): ITemplateEngine
    {
        if ($merge) {
            // if key is in first array, keep it
            // we keep existing but overwrite with new
            $this->variables = $this->convertVariables($variables) + $this->variables;
        } else {
            $this->variables = $this->convertVariables($variables);
        }

        return $this;
    }

    /**
     * replaces ITemplatable objects with its output for template assigning
     * @param array $variables
     * @return array
     */
    protected function convertVariables(array $variables): array
    {
        $temp = [];

        foreach ($variables as $key => $var) {
            if (is_array($var)) {
                $temp[$key] = $this->convertVariables($var);
            } elseif ($var instanceof ITemplatable) {
                $temp[$key] = $var->getTemplateArray();
            } else {
                $temp[$key] = $var;
            }
        }

        return $temp;
    }

    /**
     * @param \Twig_ExtensionInterface $extension
     * @return ITemplateEngine
     */
    public function registerExtension(\Twig_ExtensionInterface $extension): ITemplateEngine
    {
        $this->twigEnv->addExtension($extension);
        return $this;
    }
}