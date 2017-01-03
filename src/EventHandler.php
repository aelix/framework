<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework;


class EventHandler
{
    protected $events = [];

    /**
     * @param string $hook
     * @param callable $callable
     * @param int $priority
     * @return EventHandler
     */
    public function listen(string $hook, callable $callable, $priority = 0): self
    {
        $this->events[$hook][] = [$callable, $priority];
        return $this;
    }

    /**
     * @param string $hook
     * @param array $args
     * @return EventHandler
     */
    public function dispatch(string $hook, array $args = []): self
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

        return $this;
    }
}