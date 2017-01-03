<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

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
    public function __construct(
        \SessionHandlerInterface $sessionHandler,
        int $maxLifeTime,
        int $gcProbability,
        int $cookieLifetime = 86400,
        string $sessionName = 'aelix'
    ) {
        $this->sessionHandler = $sessionHandler;
        $this->maxLifeTime = $maxLifeTime;
        $this->gcProbability = $gcProbability;
        $this->cookieLifetime = $cookieLifetime;
        $this->sessionName = $sessionName;

        // session properties
        ini_set('session.gc_maxlifetime', (string)$maxLifeTime);
        ini_set('session.gc_probability', (string)$gcProbability);
        register_shutdown_function('session_write_close');
        session_name($sessionName);
        session_set_cookie_params($cookieLifetime, '', '', false, true);

        // register our handler
        session_set_save_handler($this->sessionHandler, true);

        // start up, if not already done
        if ($this->status() === PHP_SESSION_NONE && PHP_SAPI != 'cli') {
            session_start();
        }
    }

    /**
     * get the session status
     * @return int
     */
    public function status(): int
    {
        return session_status();
    }

    /**
     * determine if an item exists in the session
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * regenerate the session ID
     * @param bool $deleteOldSession optional
     * @return bool
     */
    public function regenerate(bool $deleteOldSession = false): bool
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * destroy and start a new session
     * @return Session
     */
    public function restart(): self
    {
        self::destroy();
        session_start();
        return $this;
    }

    /**
     * destroy the session
     * @return Session
     */
    public function destroy(): self
    {
        self::flush();
        session_destroy();
        return $this;
    }

    /**
     * remove all items from the session
     * @return Session
     */
    public function flush(): self
    {
        session_unset();
        return $this;
    }

    /**
     * @param User $user
     * @return Session
     */
    public function setUser(User $user): self
    {
        $this->set('core.user_id', $user->getID());
        return $this;
    }

    /**
     * store an item in the session
     * @param string $key
     * @param mixed $value
     * @return Session
     */
    public function set($key, $value): self
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * @return User Object GuestUser if user doesn't exist
     */
    public function getUser(): User
    {
        // TODO: guest user

        if ($this->get('core.user_id', false) === false) {
            return new GuestUser();
        }

        try {
            return User::getByID($this->get('core.user_id'));
        } catch (UserDoesntExistException $e) {
            $this->forget('core.user_id');
            return new GuestUser();
        }
    }

    /**
     * retrieve an item from the session or return a default value
     * @param string $key
     * @param mixed $default optional
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return (isset($_SESSION[$key]) ? $_SESSION[$key] : $default);
    }

    /**
     * remove an item from the session
     * @param string $key
     * @return Session
     */
    public function forget($key): self
    {
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * get an associative array suitable for assigning to template variables
     * @return array
     */
    public function getTemplateArray(): array
    {
        return $_SESSION;
    }
}