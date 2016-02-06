<?php
/**
 * @author    aelix <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\exception;


use aelix\framework\Aelix;
use aelix\framework\util\UString;

class CoreException extends LoggedException implements IPrintableException
{

    public function __construct($message = '', $code = 0, $description = '', \Throwable $previous = null)
    {
        parent::__construct((string)$message, (int)$code, $previous);
        $this->description = $description;
    }

    /**
     * Strip delicate information from shown stacktrace
     * @return string
     */
    public function __getTraceAsString()
    {
        $e = ($this->getPrevious() ?: $this);

        $string = preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e->getTraceAsString());
        $string = preg_replace('/Database->connect\(.*\)/', 'Database->connect(...)', $string);
        $string = preg_replace('/DatabaseFactory::initDatabase\(.*\)/', 'DatabaseFactory::initDatabase(...)', $string);
        $string = preg_replace('/mysqli->mysqli\(.*\)/', 'mysqli->mysqli(...)', $string);
        $string = preg_replace('/PDO->__construct\(.*\)/', 'PDO->__construct(...)', $string);

        return $string;
    }

    /**
     * Print this exception
     * @return void
     */
    public function show()
    {
        // try to log this shit
        $id = $this->logError();

        // try to get the site title
        $title = ''; // TODO: read from site configuration

        // print HTML
        @header('HTTP/1.1 503 Service Unavailable');
        $e = ($this->getPrevious() ?: $this);
        echo '<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8">
		<title>Fatal Error ' . $title . '</title>
		<style type="text/css">
			html, body { font: normal normal normal 13px Helvetica, "Droid Sans", "Segoe UI", Arial, Verdana, sans-serif; margin: 0; padding: 0; color: #555; }
			h1, h2, h3, p, pre { margin: 0; padding: 5px 10px; color: #2e2e2e; }
			h1 { background: #ffd5d5; font-size: 20px; font-weight: bold; }
			h2 { background: #eee; font-size: 16px; font-weight: bold; }
			h3 { background: #f6f6f6; margin-top: 20px; }
			table { padding: 5px 10px; border-spacing: 0; border: none; width: 100%; }
			td { padding: 2px; }
			td:first-child { font-weight: bold; color: #2e2e2e; width: 100px; }
			td:last-child { font-family: monospace; }
			tr:nth-child(2n) { background: #f6f6f6; }
		</style>
	</head>
	<body>
		<h1>Fatal Error</h1>';
        if (Aelix::isDebug()) {
            echo '		<h2>' . UString::encodeHTML($this->_getMessage()) . '</h2>
		<p>' . UString::encodeHTML($this->getDescription()) . '</p>
		<h3>Information</h3>
		<p>' . $this->information . '</p>
		<table>
			<tr>
				<td>ID</td>
				<td>' . $id . '</td>
			</tr>
			<tr>
				<td>Error message</td>
				<td>' . UString::encodeHTML($this->_getMessage()) . '</td>
			</tr>
			<tr>
				<td>Error code</td>
				<td>' . (int)$e->getCode() . '</td>
			</tr>
			<tr>
				<td>File</td>
				<td>' . UString::encodeHTML($e->getFile() . ':' . $e->getLine()) . '</td>
			</tr>
			<tr>
				<td>Time</td>
				<td>' . gmdate('r') . '</td>
			</tr>
			<tr>
				<td>Request</td>
				<td>' . (isset($_SERVER['REQUEST_URI']) ? UString::encodeHTML($_SERVER['REQUEST_URI']) : '') . '</td>
			</tr>
			<tr>
				<td>Referer</td>
				<td>' . (isset($_SERVER['HTTP_REFERER']) ? UString::encodeHTML($_SERVER['HTTP_REFERER']) : '') . '</td>
			</tr>
		</table>
		<h3>Stacktrace</h3>
		<pre>' . UString::encodeHTML($this->__getTraceAsString()) . '</pre>';
        } else {
            echo '		<table>
			<tr>
				<td>ID</td>
				<td>' . $id . '</td>
			</tr>
		</table>
		<p>Send this ID to the administrator of this website to report this issue.</p>';
        }
        echo '	</body>
</html>';
    }


}