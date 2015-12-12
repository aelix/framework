<?php
/**
 * @author    aelix framework <info@aelix framework.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\session;


use aelix\framework\template\ITemplatable;
use aelix\framework\user\GuestUser;
use aelix\framework\user\User;
use aelix\framework\user\UserDoesntExistException;

class Session implements ITemplatable
{
    /**
     * @var \SessionHandlerInterface
     */
    private $sessionHandler;

    /**
     * @var int
     */
    private $maxLifeTime;

    /**
     * @var int
     */
    private $gcProbability;

    /**
     * @var int
     */
    private $cookieLifetime;

    /**
     * @var string
     */
    private $sessionName;

    /**
     * Session constructor.
     * @param \SessionHandlerInterface $sessionHandler
     * @param int $maxLifeTime seconds
     * @param int $gcProbability probability in %
     * @param int $cookieLifetime seconds
     * @param string $sessionName
     */
    public function __construct(\SessionHandlerInterface $sessionHandler, $maxLifeTime, $gcProbability, $cookieLifetime = 86400, $sessionName = 'aelix')
    {
        $this->sessionHandler = $sessionHandler;
        $this->maxLifeTime = $maxLifeTime;
        $this->gcProbability = $gcProbability;
        $this->cookieLifetime = $cookieLifetime;
        $this->sessionName = $sessionName;

        // session properties
        ini_set('session.gc_maxlifetime', (int) $maxLifeTime);
        ini_set('session.gc_probability', (int) $gcProbability);
        register_shutdown_function('session_write_close');
        session_name($sessionName);
        session_set_cookie_params($cookieLifetime, null, null, false, true);

        // register our handler
        session_set_save_handler($this->sessionHandler, true);

        // start up, if not already done
        if ($this->status() === PHP_SESSION_NONE && PHP_SAPI != 'cli') {
            session_start();
        }
    }

    /**
     * store an item in the session
     * @param  string $key
     * @param  mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * retrieve an item from the session or return a default value
     * @param  string $key
     * @param  mixed $default optional
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return (isset($_SESSION[$key]) ? $_SESSION[$key] : $default);
    }

    /**
     * determine if an item exists in the session
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * remove an item from the session
     * @param string $key
     */
    public function forget($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * remove all items from the session
     */
    public function flush()
    {
        session_unset();
    }

    /**
     * regenerate the session ID
     * @param  bool $deleteOldSession optional
     * @return bool
     */
    public function regenerate($deleteOldSession = false)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * get the session status
     * @return int
     */
    public function status()
    {
        return session_status();
    }

    /**
     * destroy the session
     */
    public function destroy()
    {
        self::flush();
        session_destroy();
    }

    /**
     * destroy and start a new session
     */
    public function restart()
    {
        self::destroy();
        session_start();
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->set('core.user_id', $user->getID());

    }

    /**
     * @return User Object GuestUser if user doesn't exist
     */
    public function getUser()
    {
        // TODO: guest user

        if ($this->get('core.user_id', false) === false) {
            return new GuestUser();
        }

        try {
            return User::getByID($this->get('core.user_id'));
        } catch(UserDoesntExistException $e) {
            $this->forget('core.user_id');
            return new GuestUser();
        }
    }

    /**
     * get an associative array suitable for assigning to template variables
     * @return array
     */
    public function getTemplateArray()
    {
        return $_SESSION;
    }
}