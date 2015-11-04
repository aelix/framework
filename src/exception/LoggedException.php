<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\exception;


use aelix\framework\Aelix;

class LoggedException extends \Exception
{

    /**
     * Description of the exception
     * @var string
     */
    protected $description;

    /**
     * Additional information about the error
     * @var string
     */
    protected $information;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Hide the real message from being displayed when not in debug mode
     * @return string
     */
    public function _getMessage()
    {
        if (Aelix::isDebug()) {
            $e = ($this->getPrevious() ?: $this);
            return $e->getMessage();
        }

        return 'You\'ve encountered an error. Please send the displayed ID to the site admin.';
    }

    /**
     * Log this exception.
     * @return string ID of the logged exception
     */
    protected function logError()
    {
        $logDir = DIR_ROOT . 'logs/';
        $logFilePath = $logDir . date('Y-m-d') . '.log';

        // check for directory and create
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        @touch($logFilePath);

        // check file
        if (!is_file($logFilePath) || !is_writable($logFilePath)) {
            return 'Unable to write log file';
        }

        // get repacked exception
        $e = ($this->getPrevious() ?: $this);

        $text = date('r') . NL .
            'Message: ' . $e->getMessage() . NL .
            'Description: ' . $this->description . NL .
            'File: ' . $e->getFile() . ':' . $e->getLine() . NL .
            'Request URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . NL .
            'Referrer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') . NL .
            'Additional information: ' . NL . $this->information . NL . NL .
            'Stacktrace: ' . NL . implode(NL . ' ', explode("\n", $e->getTraceAsString())) . NL;

        $id = sha1($text);
        $message = '----- ' . $id . ' -----' . NL . $text . NL . NL;

        // write to file
        file_put_contents($logFilePath, $message, FILE_APPEND);

        return $id;
    }

}