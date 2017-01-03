<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

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
     * @var Route[]
     */
    protected $namedRoutes;

    /**
     * Router constructor.
     * @param string $basePath
     * @param RouteCollection $routeCollection
     */
    public function __construct(string $basePath = '', ?RouteCollection $routeCollection = null)
    {
        $this->basePath = rtrim($basePath, '/');

        if ($routeCollection === null) {
            $this->routeCollection = new RouteCollection();
        } else {
            $this->routeCollection = $routeCollection;

            foreach ($this->routeCollection as $route) {
                /** @var $route Route */
                if ($route->getName()) {
                    $this->namedRoutes[$route->getName()] = $route;
                }
            }

        }
    }

    /**
     * @see \aelix\framework\route\Route::__construct()
     * @param string $name
     * @param string|array $method
     * @param string $url
     * @param callable $handler
     * @return Router
     */
    public function map(string $name = '', $method, string $url, callable $handler): Router
    {
        $newRoute = new Route($name, $method, $url, $handler);
        $this->routeCollection->add($newRoute);
        if ($newRoute->getName()) {
            $this->namedRoutes[$newRoute->getName()] = $newRoute;
        }
        return $this;
    }

    /**
     * @return Route
     */
    public function matchCurrentRequest(): Route
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
     * @return Route
     * @throws NoRouteMatchedException
     */
    public function match($requestURL, $requestMethod): Route
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

        throw new NoRouteMatchedException();
    }

    /**
     * @param Route $route
     * @return Router
     */
    public function addRoute(Route $route): Router
    {
        $this->routeCollection->add($route);
        return $this;
    }

    /**
     * @param RouteCollection $routeCollection
     * @return Router
     */
    public function setRouteCollection(RouteCollection $routeCollection): Router
    {
        $this->routeCollection = $routeCollection;
        return $this;
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @return string
     * @throws NamedRouteNotFoundException
     */
    public function buildUrl(string $routeName, array $parameters = []): string
    {

        if (isset($this->namedRoutes[$routeName])) {
            return $this->buildUrlFromRoute($this->namedRoutes[$routeName], $parameters);
        } else {
            throw new NamedRouteNotFoundException();
        }

    }

    /**
     * @param Route $route
     * @param array $parameters
     * @return string
     */
    public function buildUrlFromRoute(Route $route, array $parameters = []): string
    {
        $rawUrl = $route->getUrl();
        $builtUrl = $rawUrl; // if no parameters, use raw URL

        if ($parameters && preg_match_all('/:(\w+)/', $rawUrl, $paramKeys)) {
            // get the matches
            $paramKeys = $paramKeys[1];

            foreach ($paramKeys as $key) {
                if (isset($parameters[$key])) {
                    // replace one by one
                    $builtUrl = preg_replace('/:(\w+)/', $parameters[$key], $builtUrl, 1);
                }
            }
        }

        return $builtUrl;
    }
}