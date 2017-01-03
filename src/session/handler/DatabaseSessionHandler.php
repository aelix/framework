<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\session\handler;


use aelix\framework\database\Database;

class DatabaseSessionHandler implements \SessionHandlerInterface
{

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * DatabaseSessionHandler constructor.
     * @param Database $db
     * @param string $tableName
     */
    public function __construct(Database $db, string $tableName)
    {
        $this->db = $db;
        $this->tableName = $tableName;
    }


    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function destroy($session_id): bool
    {
        return (bool)$this->db->prepare('DELETE FROM `' . $this->tableName . '` WHERE `id` = :id')
            ->execute([
                ':id' => $session_id
            ]);
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function gc($maxlifetime): bool
    {
        return (bool)$this->db->prepare('DELETE FROM `' . $this->tableName . '` WHERE `lastActivity` = :maxlifetime')
            ->execute([
                ':maxlifetime' => (time() - $maxlifetime)
            ]);
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function open($save_path, $session_id): bool
    {
        return true;
    }

    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function read($session_id): string
    {
        $result = $this->db->prepare('SELECT * FROM `' . $this->tableName . '` WHERE `id` = :id')
            ->execute([
                ':id' => $session_id
            ])
            ->fetchArray();

        return ($result ? $result['sessionData'] : '');
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4.0
     */
    public function write($session_id, $session_data): bool
    {
        // check if session exists
        $s = $this->db->prepare('SELECT COUNT(*) FROM `' . $this->tableName . '` WHERE `id` = :id')
            ->execute([
                ':id' => $session_id
            ]);

        if ($s->fetchArray()['COUNT(*)'] == 0) {
            // create a new session
            return (bool)$this->db->prepare('INSERT INTO `' . $this->tableName . '` (`id`, `sessionData`, `lastActivity`) VALUES (:id, :data, :time)')
                ->execute([
                    ':id' => $session_id,
                    ':data' => $session_data,
                    ':time' => time()
                ]);
        }

        // session exists, update
        return (bool)$this->db->prepare('UPDATE `' . $this->tableName . '` SET `sessionData` = :data, `lastActivity` = :time WHERE `id` = :id')
            ->execute([
                ':id' => $session_id,
                ':time' => time(),
                ':data' => $session_data
            ]);
    }
}