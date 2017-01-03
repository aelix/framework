<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\route;


class RouteCollection extends \SplObjectStorage
{
    /**
     * add a route to this collection
     * @param Route $route
     * @return RouteCollection
     */
    public function add(Route $route): self
    {
        parent::attach($route, null);
        return $this;
    }

    /**
     * get all routes
     * @return Route[]
     */
    public function getAll(): array
    {
        $return = [];

        foreach ($this as $route) {
            $return[] = $route;
        }

        return $return;
    }
}