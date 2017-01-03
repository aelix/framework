<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\route;

/**
 * Class Route. Inspired by https://github.com/dannyvankooten/PHP-Router/blob/master/src/PHPRouter/Router.php
 * @package aelix\framework\route
 */
class Route
{

    /**
     * available HTTP methods
     */
    const HTTP_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * HTTP method
     * @var array
     */
    protected $methods;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * name of this route (for reverse building)
     * @var string
     */
    protected $name;

    /**
     * Route constructor.
     * @param string $name
     * @param string|array $methods
     * @param string $url
     * @param callable $handler
     */
    public function __construct(string $name, $methods, string $url, callable $handler)
    {
        // check methods and set
        if (is_array($methods)) {
            foreach ($methods as $method) {
                if (!in_array($method, self::HTTP_METHODS)) {
                    throw new \InvalidArgumentException($method . ' is not a valid HTTP method.');
                }
            }
            $this->methods = $methods;
        } else {
            if (!in_array($methods, self::HTTP_METHODS)) {
                throw new \InvalidArgumentException($methods . ' is not a valid HTTP method.');
            }
            $this->methods = [$methods];
        }

        // remove last slash
        $this->url = ($url = '/' ? $url : rtrim($url, '/'));
        $this->handler = $handler;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return preg_replace('/(:\w+)/', '([\w-%]+)', $this->url);
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param array $parameters
     * @return Route
     */
    public function setParameters(array $parameters): Route
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * fire registered handler
     * @return Route
     */
    public function dispatch(): Route
    {
        call_user_func_array($this->handler, $this->parameters);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

}