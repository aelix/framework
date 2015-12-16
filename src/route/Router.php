<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\route;

/**
 * Class Router. Inspired by https://github.com/dannyvankooten/PHP-Router/blob/master/src/PHPRouter/Router.php
 * @package aelix\framework\route
 */
class Router
{
    /**
     * @var RouteCollection
     */
    protected $routeCollection;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * Router constructor.
     * @param string $basePath
     * @param RouteCollection|null $routeCollection
     */
    public function __construct($basePath = '', RouteCollection $routeCollection = null)
    {
        $this->basePath = rtrim($basePath, '/');

        if ($routeCollection === null) {
            $this->routeCollection = new RouteCollection();
        } else {
            $this->routeCollection = $routeCollection;
        }
    }

    public function matchCurrentRequest()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];

        // strip GET variables, not important for routing
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }

        return $this->match($requestUrl, $requestMethod);
    }

    /**
     * Inspired
     * @param $requestURL
     * @param $requestMethod
     * @return bool|Route
     */
    public function match($requestURL, $requestMethod)
    {
        foreach ($this->routeCollection->getAll() as $route) {

            // compare methods
            if (!in_array($requestMethod, $route->getMethods())) {
                continue; // doesn't match request method
            }

            // strip subdirectory
            $currentDir = dirname($_SERVER['SCRIPT_NAME']);
            if ($currentDir != '/') {
                $requestURL = str_replace($currentDir, '', $requestURL);
            }

            if (!preg_match('@^' . $this->basePath . $route->getRegex() . '@i', $requestURL, $matches)) {
                continue; // doesn't match url
            }

            $params = [];

            if (preg_match_all('/:([\w-%]+)/', $route->getUrl(), $argumentKeys)) {
                $argumentKeys = $argumentKeys[1];

                foreach ($argumentKeys as $key => $name) {
                    if (isset($matches[$key + 1])) {
                        $params[$name] = $matches[$key + 1];
                    }
                }
            }

            $route->setParameters($params);
            return $route;
        }
        return false;
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        $this->routeCollection->add($route);
        return $this;
    }

    /**
     * @param RouteCollection $routeCollection
     * @return $this
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
        return $this;
    }
}