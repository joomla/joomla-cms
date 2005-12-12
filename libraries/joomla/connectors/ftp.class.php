<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

if (!defined('CRLF')) {
	define('CRLF', "\r\n");
}
if (!defined("FTP_AUTOASCII")) {
	define("FTP_AUTOASCII", -1);
}
if (!defined("FTP_BINARY")) {
	define("FTP_BINARY", 1);
}
if (!defined("FTP_ASCII")) {
	define("FTP_ASCII", 0);
}


/**
 * FTP client class
 * 
 * @author Louis Landry  <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JFTP extends JObject {

	/**
	 * Server connection resource
	 *
	 * @access private
	 */
	var $_conn = null;

	/**
	 * Data port connection resource
	 *
	 * @access private
	 */
	var $_dataconn = null;

	/**
	 * Passive connection information
	 *
	 * @access private
	 */
	var $_pasv = null;

	/**
	 * Response Message
	 *
	 * @access private
	 */
	var $_response = null;

	/**
	 * Error Message
	 *
	 * @access private
	 */
	var $_error = null;

	/**
	 * Transfer Type
	 *
	 * @access private
	 */
	var $_type = null;

	/**
	 * Array to hold ascii format file extensions
	 *
	 * @final
	 * @access private
	 */
	var $_autoAscii = array ("asp", "bat", "c", "cpp", "csv", "h", "htm", "html", "shtml", "ini", "inc", "log", "php", "php3", "pl", "perl", "sh", "sql", "txt", "xhtml", "xml");

	/**
	 * Array to hold native line ending characters
	 *
	 * @final
	 * @access private
	 */
	var $_lineEndings = array ('UNIX' => "\n", 'MAC' => "\r", 'WIN' => "\r\n");
	
	function __construct($options) {

		// If default transfer type is no set, set it to autoascii detect
		if (!isset ($options['type'])) {
			$options['type'] = FTP_AUTOASCII;
		}

		$this->setOptions($options);
	}

	function __destruct() {
	}

	/**
	 * Returns a reference to the global FTP connector object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $ftp = &JFTP::getInstance($host);</pre>
	 *
	 * @param string $host Host to connect to
	 * @return JFTP  The FTP Client object.
	 * @since 1.1
	 */
	function & getInstance($host = 'localhost', $options = null) {
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
			$instances[$host] = new JFTP($options);
			$instances[$host]->connect($host);
		}

		if (!is_object($instances[$host])) {
			$instances[$host] = new JFTP($options);
			$instances[$host]->connect($host);
		} else {
			// If instance already exists, set options for this use
			$instances[$host]->connect($host);
			$instances[$host]->setOptions($options);
		}

		return $instances[$host];
	}

	/**
	 * Set client options
	 *
	 * @access public
	 * @param array $options Associative array of options to set
	 * @return boolean True if successful
	 */
	function setOptions($options) {

		if (isset ($option['type'])) {
			$this->_type = $options['type'];
		}

		return true;
	}

	/**
	 * Method to connect to a FTP server
	 *
	 * @access public
	 * @param string $host Host to connect to [Default: localhost]
	 * @param string $port Port to connect on [Default: port 21]
	 * @return boolean True if successful
	 */
	function connect($host = 'localhost', $port = 21) {

		//Initialize variables
		$errno = null;
		$err = null;

		// Connect to the FTP server.
		$this->_conn = @ fsockopen($host, $port, $errno, $err, 5);
		if (!$this->_conn) {
			$this->_logError($errno." - ".$err);
			return false;
		}

		// Check for welcome response code
		if (!$this->_verifyResponse(220)) {
			$this->_logError('FTP Connect: Bad Response');
			return false;
		}

		return true;
	}

	/**
	 * Method to login to a server once connected
	 *
	 * @access public
	 * @param string $user Username to login to the server
	 * @param string $pass Password to login to the server
	 * @return boolean True if successful
	 */
	function login($user = 'anonymous', $pass = 'jftp@joomla.org') {

		// Send the username
		if (!$this->_putCmd('USER '.$user, 331)) {
			$this->_logError('FTP Login: Bad Username'.$user);
			return false;
		}

		// Send the password
		if (!$this->_putCmd('PASS '.$pass, 230)) {
			$this->_logError('FTP Login: Bad Password'.$pass);
			return false;
		}

		return true;
	}

	/**
	 * Method to quit and close the connection
	 *
	 * @access public
	 * @return boolean True if successful
	 */
	function quit() {

		// Logout and close connection
		fwrite($this->_conn, "QUIT\r\n");
		fclose($this->_conn);

		return true;
	}

	/**
	 * Method to retrieve the current working directory on the FTP server
	 *
	 * @access public
	 * @return string Current working directory
	 */
	function pwd() {

		// Initialize variables
		$match = array (null);

		// Send print working directory command and verify success
		if (!$this->_putCmd('PWD', 257)) {
			$this->_logError('FTP PWD: Unable to retrieve path');
			return false;
		}

		// Match just the path
		preg_match('/"[^"\r\n]*"/', $this->_response, $match);

		// Return the cleaned path
		return preg_replace("/\"/", "", $match[0]);
	}

	/**
	 * Method to change the current working directory on the FTP server
	 *
	 * @access public
	 * @param string $path Path to change into on the server
	 * @return boolean True if successful
	 */
	function chdir($path) {

		// Strip trailing slash if it exists
		$path = rtrim($path, '/');

		// Send change directory command and verify success
		if (!$this->_putCmd('CWD '.$path, 250)) {
			$this->_logError('FTP Chdir: Bad Path: '.$path);
			return false;
		}

		return true;
	}

	/**
	 * Method to reinitialize the server, ie. need to login again
	 *
	 * NOTE: This command not available on all servers
	 *
	 * @access public
	 * @return boolean True if successful
	 */
	function reinit() {

		// Send reinitialize command to the server
		if (!$this->_putCmd('REIN', 220)) {
			$this->_logError('FTP Reinitialize: Failed');
			return false;
		}

		return true;
	}

	/**
	 * Method to rename a file/folder on the FTP server
	 *
	 * @access public
	 * @param string $from Path to change file/folder from
	 * @param string $to Path to change file/folder to
	 * @return boolean True if successful
	 */
	function rename($from, $to) {

		// Send rename from command to the server
		if (!$this->_putCmd('RNFR '.$from, 350)) {
			$this->_logError('FTP Rename: Unable to rename '.$from.' to '.$to);
			return false;
		}

		// Send rename to command to the server
		if (!$this->_putCmd('RNTO '.$to, 250)) {
			$this->_logError('FTP Rename: Unable to rename '.$from.' to '.$to);
			return false;
		}

		return true;
	}

	/**
	 * Method to change mode for a path on the FTP server
	 *
	 * @access public
	 * @param string $path Path to change mode on
	 * @param string $mode Octal value to change mode to
	 * @return boolean True if successful
	 */
	function chmod($path, $mode) {

		// If no filename is given, we assume the current directory is the target
		if ($path == '') {
			$path = '.';
		}

		// Send change mode command and verify success [must convert mode from octal]
		if (!$this->_putCmd('SITE CHMOD '.decoct($mode).' '.$path, 200)) {
			$this->_logError('FTP Chmod: Unable to change '.$path.' to '.$mode);
			return false;
		}
		return true;
	}

	/**
	 * Method to delete a path [file/folder] on the FTP server
	 *
	 * @access public
	 * @param string $path Path to delete
	 * @return boolean True if successful
	 */
	function delete($path) {

		// Send delete file command and if that doesn't work, try to remove a directory
		if (!$this->_putCmd('DELE '.$path, 250)) {
			if (!$this->_putCmd('RMD '.$path, 250)) {
				$this->_logError('FTP Delete: Unable to remove: '.$path);
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to create a directory on the FTP server
	 *
	 * @access public
	 * @param string $path Directory to create
	 * @return boolean True if successful
	 */
	function mkdir($path) {

		// Send change directory command and verify success
		if (!$this->_putCmd('MKD '.$path, 257)) {
			$this->_logError('FTP Mkdir: Unable to create: '.$path);
			return false;
		}

		return true;
	}

	/**
	 * Method to restart data transfer at a given byte
	 *
	 * @access public
	 * @param int $point Byte to restart transfer at
	 * @return boolean True if successful
	 */
	function restart($point) {

		// Send restart command and verify success
		if (!$this->_putCmd('REST '.$point, 350)) {
			$this->_logError('FTP Restart: Unable to restart at: '.$point);
			return false;
		}

		return true;
	}

	/**
	 * Method to create an empty file on the FTP server
	 *
	 * @access public
	 * @param string $path Path local file to store on the FTP server
	 * @return boolean True if successful
	 */
	function create($path) {

		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP Store: Unable to use passive mode');
			return false;
		}

		if (!$this->_putCmd('STOR '.$path, array (150, 125))) {
			$this->_logError('FTP ListDir: Response not successful');
			@ fclose($this->_dataconn);
			return false;
		}

		// To create a zero byte upload close the data port connection
		fclose($this->_dataconn);

		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP Store: Unable to store: '.$path);
			return false;
		}

		return true;
	}

	/**
	 * Method to read a file from the FTP server's contents into a buffer
	 *
	 * @access public
	 * @param string $remote Path to remote file to read on the FTP server
	 * @param string $buffer Buffer variable to read file contents into
	 * @return boolean True if successful
	 */
	function read($remote, & $buffer) {

		// Initialize variables
		$matches = array (null);

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			preg_match("/\..+/", $remote, $matches);
			$ext = strtolower(substr($matches[0], 1));

			if (in_array($ext, $this->_autoAscii)) {
				$mode = FTP_ASCII;
			} else {
				$mode = FTP_BINARY;
			}
		}
		elseif ($this->_type == FTP_ASCII) {
			$mode = FTP_ASCII;
		} else {
			$mode = FTP_BINARY;
		}

		$this->_mode($mode);
		$this->restart(0);


		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP Read: Unable to use passive mode');
			return false;
		}

		if (!$this->_putCmd('RETR '.$remote, array (150, 125))) {
			$this->_logError('FTP Read: Unable to send retrieve command');
			@ fclose($this->_dataconn);
			return false;
		}

		// Read data from data port connection and add to the buffer
		while (!feof($this->_dataconn)) {
			$buffer .= fread($this->_dataconn, 4096);
		}

		// Close the data port connection
		fclose($this->_dataconn);

		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP Write: Unable to store data at: '.$remote);
			return false;
		}

		return true;
	}

	/**
	 * Method to get a file from the FTP server and save it to a local file
	 *
	 * @access public
	 * @param string $local Path to local file to save remote file as
	 * @param string $remote Path to remote file to get on the FTP server
	 * @return boolean True if successful
	 */
	function get($local, $remote) {

		// Initialize variables
		$matches = array (null);

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			preg_match("/\..+/", $remote, $matches);
			$ext = strtolower(substr($matches[0], 1));

			if (in_array($ext, $this->_autoAscii)) {
				$mode = FTP_ASCII;
			} else {
				$mode = FTP_BINARY;
			}
		}
		elseif ($this->_type == FTP_ASCII) {
			$mode = FTP_ASCII;
		} else {
			$mode = FTP_BINARY;
		}

		$this->_mode($mode);
		$this->restart(0);

		// Check to see if the local file can be opened for writing
		$fp = fopen($local, "w");
		if (!$fp) {
			$this->_logError('FTP Get: Couldn\'t write to: '.$local);
			return false;
		}


		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP Get: Unable to use passive mode');
			return false;
		}

		if (!$this->_putCmd('RETR '.$remote, array (150, 125))) {
			$this->_logError('FTP Get: Unable to send retrieve command');
			@ fclose($this->_dataconn);
			return false;
		}

		// Read data from data port connection and add to the buffer
		while (!feof($this->_dataconn)) {
			$buffer = fread($this->_dataconn, 4096);
			$ret = fwrite($fp, $buffer, 4096);
		}

		// Close the data port connection and file pointer
		fclose($this->_dataconn);
		fclose($fp);

		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP Get: Unable to get data from: '.$remote);
			return false;
		}

		return true;
	}

	/**
	 * Method to store a file to the FTP server
	 *
	 * @access public
	 * @param string $local Path to local file to store on the FTP server
	 * @param string $remote FTP path to file to create
	 * @return boolean True if successful
	 */
	function store($local, $remote = null) {

		// Initialize variables
		$matches = array (null);
		$mode = null;

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			preg_match("/\..+/", $local, $matches);
			$ext = strtolower(substr($matches[0], 1));

			if (in_array($ext, $this->_autoAscii)) {
				$mode = FTP_ASCII;
			} else {
				$mode = FTP_BINARY;
			}
		}
		elseif ($this->_type == FTP_ASCII) {
			$mode = FTP_ASCII;
		} else {
			$mode = FTP_BINARY;
		}

		$this->_mode($mode);
		$this->restart(0);

		// If remote file not given, use the filename of the local file in the current
		// working directory
		if ($remote == null) {
			$remote = basename($local);
		}

		// Check to see if the local file exists and open for reading if so
		if (@ file_exists($local)) {
			$fp = fopen($local, "r");
			if (!$fp) {
				$this->_logError('FTP Store: Couldn\'t read: '.$local);
				return false;
			}
		} else {
			$this->_logError('FTP Store: Couldn\'t find: '.$local);
			return false;
		}

		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP Store: Unable to use passive mode');
			@ fclose($fp);
			return false;
		}

		// Send store command to the FTP server
		if (!$this->_putCmd('STOR '.$remote, array (150, 125))) {
			$this->_logError('FTP Store: Response not successful');
			@ fclose($fp);
			@ fclose($this->_dataconn);
			return false;
		}

		// Do actual file transfer, read local file and write to data port connection
		while (!feof($fp)) {
			$line = fread($fp, 4096);
			do {
				if (($result = @ fwrite($this->_dataconn, $line)) === false) {
					$this->_logError('FTP Store: Unable to write to data port socket');
					return false;
				}
				$line = substr($line, $result);
			} while ($line != "");
		}

		fclose($fp);
		fclose($this->_dataconn);

		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP Write: Unable to store: '.$local);
			return false;
		}

		return true;
	}

	/**
	 * Method to write a string to the FTP server
	 *
	 * @access public
	 * @param string $remote FTP path to file to write to
	 * @param string $buffer Contents to write to the FTP server
	 * @param int $mode Transfer mode [1:Binary|0:Ascii] or use constants FTP_BINARY or FTP_ASCII [Defaults to Ascii]
	 * @return boolean True if successful
	 */
	function write($remote, $buffer, $mode = FTP_ASCII) {

		$this->_mode($mode);
		$this->restart(0);


		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP Write: Unable to use passive mode');
			return false;
		}

		// Send store command to the FTP server
		if (!$this->_putCmd('STOR '.$remote, array (150, 125))) {
			$this->_logError('FTP Write: Response not successful');
			@ fclose($this->_dataconn);
			return false;
		}

		// Write buffer to the data connection port
		do {
			if (($result = @ fwrite($this->_dataconn, $buffer)) === false) {
				$this->_logError('FTP Write: Unable to write to data port');
				return false;
			}
			$buffer = substr($buffer, $result);
		} while ($buffer != "");

		// Close the data connection port [Data transfer complete]
		fclose($this->_dataconn);

		// Verify that the server recieved the transfer
		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP Write: Unable to store data at: '.$remote);
			return false;
		}

		return true;
	}

	/**
	 * Method to list the contents of a directory on the FTP server
	 *
	 * @access public
	 * @param string $path Path local file to store on the FTP server
	 * @param boolean $search Recursively search subdirectories
	 * @return string Directory listing
	 */
	function listDir($path = '', $recurse = false) {

		// Initialize variables
		$data = null;

		// Start passive mode
		if (!$this->_passive()) {
			$this->_logError('FTP ListDir: Unable to use passive mode');
			return false;
		}

		if (!$this->_putCmd('NLST '.$path, array (150, 125))) {
			$this->_logError('FTP ListDir: Response not successful');
			@ fclose($this->_dataconn);
			return false;
		}

		// Read in the file listing.
		while (!feof($this->_dataconn)) {
			$data .= fread($this->_dataconn, 4096);
		}
		fclose($this->_dataconn);

		// Everything go okay?
		if (!$this->_verifyResponse(226)) {
			$this->_logError('FTP ListDir: Unable to list directory contents of: '.$path);
			return false;
		}

		return preg_split("/[".CRLF."]+/", $data, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Send command to the FTP server and validate an expected response code
	 *
	 * @access private
	 * @param string $cmd Command to send to the FTP server
	 * @param mixed $expected Integer response code or array of integer response codes
	 * @return boolean True if command executed successfully
	 */
	function _putCmd($cmd, $expectedResponse) {

		// Make sure we have a connection to the server
		if (!is_resource($this->_conn)) {
			$this->_logError('FTP Put Command: Not Connected');
			return false;
		}

		// Send the command to the server
		if (!fwrite($this->_conn, $cmd."\r\n")) {
			$this->_logError('FTP Put Command: Unable to send: '.$cmd.' command');
		}

		return $this->_verifyResponse($expectedResponse);
	}

	/**
	 * Verify the response code from the server and log response if flag is set
	 *
	 * @access private
	 * @param mixed $expected Integer response code or array of integer response codes
	 * @return boolean True if response code from the server is expected
	 */
	function _verifyResponse($expected) {

		// Wait for a response from the server, but timeout in 5 seconds
		$time = time();
		do {
			$this->_response = fgets($this->_conn, 1024);
		} while (substr($this->_response, 3, 1) != ' ' && time() - $time < 5);

		// Separate the code from the message
		$responseCode = substr($this->_response, 0, 3);
		$responseMsg = substr($this->_response, 4);

		// Did the server respond with the code we wanted?
		if (is_array($expected)) {
			if (in_array($responseCode, $expected)) {
				$retval = true;
			} else {
				$retval = false;
			}
		} else {
			if ($responseCode == $expected) {
				$retval = true;
			} else {
				$retval = false;
			}
		}
		return $retval;
	}

	/**
	 * Set server to passive mode and open a data port connection
	 *
	 * @access private
	 * @return boolean True if successful
	 */
	function _passive() {

		//Initialize variables
		$match = array (null);
		$errno = null;
		$err = null;

		// Make sure we have a connection to the server
		if (!is_resource($this->_conn)) {
			$this->_logError('FTP Passive: Not Connected');
			return false;
		}

		// Request a passive connection - this means, we'll talk to you, you don't talk to us.
		@ fwrite($this->_conn, "PASV\r\n");
		$time = time();
		do $response = fgets($this->_conn, 1024);
		while (substr($response, 3, 1) != ' ' && time() - $time < 5);

		// If it's not 227, we weren't given an IP and port, which means it failed.
		if (substr($response, 0, 4) != '227 ') {
			$this->_logError('FTP Passive: Unable to obtain IP and port for data transfer');
			return false;
		}

		// Snatch the IP and port information, or die horribly trying...
		if (preg_match('~\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))\)~', $response, $match) == 0) {
			$this->_logError('FTP Passive: IP and port not valid');
			return false;
		}

		// This is pretty simple - store it for later use ;).
		$this->_pasv = array ('ip' => $match[1].'.'.$match[2].'.'.$match[3].'.'.$match[4], 'port' => $match[5] * 256 + $match[6]);

		// Connect, assuming we've got a connection.
		$this->_dataconn = @ fsockopen($this->_pasv['ip'], $this->_pasv['port'], $errno, $err, 5);
		if (!$this->_dataconn) {
			$this->_logError('FTP ListDir: Unable to open data port connection');
			return false;
		}

		return true;
	}

	/**
	 * Set transfer mode
	 *
	 * @access private
	 * @param int $mode Integer representation of data transfer mode [1:Binary|0:Ascii]
	 *  Defined constants can also be used [FTP_BINARY|FTP_ASCII]
	 * @return boolean True if successful
	 */
	function _mode($mode) {
		if ($mode == FTP_BINARY) {
			if (!$this->_putCmd("TYPE I", 200)) {
				$this->_logError('FTP Mode: Unable to set mode to binary');
				return false;
			}
		} else {
			if (!$this->_putCmd("TYPE A", 200)) {
				$this->_logError('FTP Mode: Unable to set mode to ascii');
				return false;
			}
		}
		return true;
	}

	/**
	 * Log the error message
	 *
	 * @access private
	 * @param string $msg Error message to add to the queue
	 * @return boolean True if successful
	 */
	function _logError($msg = '') {
		$this->_error .= $msg."\r\n";
		return true;
	}
}
?>