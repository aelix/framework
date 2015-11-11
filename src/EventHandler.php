<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework;


class EventHandler
{
    protected $events = [];

    /**
     * @param string $hook
     * @param callable $callable
     * @param int $priority
     */
    public function listen($hook, callable $callable, $priority = 0)
    {
        $this->events[$hook][] = [$callable, $priority];
    }

    /**
     * @param string $hook
     * @param array $args
     */
    public function dispatch($hook, array $args = [])
    {
        if (isset($this->events[$hook])) {
            // additional call parameters
            //$args = func_get_args();
            //array_shift($args);
            // sort listeners
            usort($this->events[$hook], function ($a, $b) {
                if ($a[1] > $b[1] && $a[1] != 0) {
                    return -1;
                }
                if ($a[1] < $b[1] && $b[1] != 0) {
                    return 1;
                }
                return 0;
            });
            // call the registered functions
            foreach ($this->events[$hook] as $listener) {
                call_user_func_array($listener[0], $args);
            }
        }
    }
}