<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\route;

/**
 * Class Route. Inspired by https://github.com/dannyvankooten/PHP-Router/blob/master/src/PHPRouter/Router.php
 * @package aelix\framework\route
 */
class Route
{

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
     * Route constructor.
     * @param string|array $methods
     * @param string $url
     * @param callable $handler
     */
    public function __construct($methods, $url, callable $handler)
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
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return preg_replace('/(:\w+)/', '([\w-%]+)', $this->url);
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * fire registered handler
     */
    public function dispatch()
    {
        call_user_func_array($this->handler, $this->parameters);
    }

}