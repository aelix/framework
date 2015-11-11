<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\route;


class RouteCollection extends \SplObjectStorage
{
    /**
     * add a route to this collection
     * @param Route $route
     */
    public function add(Route $route)
    {
        parent::attach($route, null);
    }

    /**
     * get all routes
     * @return Route[]
     */
    public function getAll()
    {
        $return = [];

        foreach ($this as $route) {
            $return[] = $route;
        }

        return $return;
    }
}