<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/** Error Codes:
 *  - 30 : Unable to connect to host
 *  - 31 : Not connected
 *  - 32 : Unable to send command to server
 *  - 33 : Bad username
 *  - 34 : Bad password
 *  - 35 : Bad response
 *  - 36 : Passive mode failed
 *  - 37 : Data transfer error
 *  - 38 : Local filesystem error
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
 * @author		Louis Landry  <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Connector
 * @since		1.5
 */
class JFTP extends JObject {

	/**
	 * Server connection resource
	 *
	 * @access private
	 * @var socket resource
	 */
	var $_conn = null;

	/**
	 * Data port connection resource
	 *
	 * @access private
	 * @var socket resource
	 */
	var $_dataconn = null;

	/**
	 * Passive connection information
	 *
	 * @access private
	 * @var array
	 */
	var $_pasv = null;

	/**
	 * Response Message
	 *
	 * @access private
	 * @var string
	 */
	var $_response = null;

	/**
	 * Transfer Type
	 *
	 * @access private
	 * @var int
	 */
	var $_type = null;

	/**
	 * Native OS Type
	 *
	 * @access private
	 * @var string
	 */
	var $_OS = null;

	/**
	 * Array to hold ascii format file extensions
	 *
	 * @final
	 * @access private
	 * @var array
	 */
	var $_autoAscii = array ("asp", "bat", "c", "cpp", "csv", "h", "htm", "html", "shtml", "ini", "inc", "log", "php", "php3", "pl", "perl", "sh", "sql", "txt", "xhtml", "xml");

	/**
	 * Array to hold native line ending characters
	 *
	 * @final
	 * @access private
	 * @var array
	 */
	var $_lineEndings = array ('UNIX' => "\n", 'MAC' => "\r", 'WIN' => "\r\n");

	function __construct($options) {

		// If default transfer type is no set, set it to autoascii detect
		if (!isset ($options['type'])) {
			$options['type'] = FTP_AUTOASCII;
		}
		$this->setOptions($options);

		if (JPATH_ISWIN) {
			$this->_OS = 'WIN';
		} elseif (JPATH_ISMAC) {
			$this->_OS = 'MAC';
		} else {
			$this->_OS = 'UNIX';
		}


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
	 * @since 1.5
	 */
	function & getInstance($host = '127.0.0.1', $port = '21', $options = null) {
		static $instances;

		$signature = $host.":".$port;
		
		if (!isset ($instances)) {
			$instances = array ();
			$instances[$signature] = new JFTP($options);
			$instances[$signature]->connect($host, $port);
		}

		if (!is_object($instances[$signature])) {
			$instances[$signature] = new JFTP($options);
			$instances[$signature]->connect($host, $port);
		} else {
			// If instance already exists, set options for this use
			$instances[$signature]->connect($host, $port);
			$instances[$signature]->setOptions($options);
		}

		return $instances[$signature];
	}

	/**
	 * Set client options
	 *
	 * @access public
	 * @param array $options Associative array of options to set
	 * @return boolean True if successful
	 */
	function setOptions($options) {

		if (isset ($options['type'])) {
			$this->_type = $options['type'];
		}
		return true;
	}

	/**
	 * Method to connect to a FTP server
	 *
	 * @access public
	 * @param string $host Host to connect to [Default: 127.0.0.1]
	 * @param string $port Port to connect on [Default: port 21]
	 * @return boolean True if successful
	 */
	function connect($host = '127.0.0.1', $port = 21) {
		
		// Initialize variables
		$errno = null;
		$err = null;

		/*
		 * If not already connected, connect
		 */
		if (!is_resource($this->_conn)) {

			// Connect to the FTP server.
			$this->_conn = @ fsockopen($host, $port, $errno, $err, 5);
			if (!$this->_conn) {
				JError::raiseError('30', 'JFTP::connect: Could not connect to host:'.$host.' on port:'.$port.'.', 'Socket error number:'.$errno.' and error message:'.$err );
				return false;
			}
	
			// Check for welcome response code
			if (!$this->_verifyResponse(220)) {
				JError::raiseError('35', 'JFTP::connect: Bad response.', 'Server response:'.$this->_response.' [Expected: 220]' );
				return false;
			}
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
			JError::raiseWarning('33', 'JFTP::login: Bad Username.', 'Server response:'.$this->_response.' [Expected: 331] Username sent:'.$user );
			return false;
		}

		// Send the password
		if (!$this->_putCmd('PASS '.$pass, 230)) {
			JError::raiseWarning('34', 'JFTP::login: Bad Password.', 'Server response:'.$this->_response.' [Expected: 230] Password sent:'.$pass );
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
		@fwrite($this->_conn, "QUIT\r\n");
		@fclose($this->_conn);

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
			JError::raiseError('35', 'JFTP::pwd: Bad response.', 'Server response:'.$this->_response.' [Expected: 257]' );
			return false;
		}

		// Match just the path
		preg_match('/"[^"\r\n]*"/', $this->_response, $match);

		// Return the cleaned path
		return preg_replace("/\"/", "", $match[0]);
	}

	/**
	 * Method to system string from the FTP server
	 *
	 * @access public
	 * @return string System identifier string
	 */
	function syst() {

		// Initialize variables
		$match = array (null);

		// Send print working directory command and verify success
		if (!$this->_putCmd('SYST', 215)) {
			JError::raiseError('35', 'JFTP::syst: Bad response.', 'Server response:'.$this->_response.' [Expected: 215]' );
			return false;
		}

		// Match the system string to an OS
		if (!(strpos('MAC', strtoupper($this->_response)) === false)) {
			$ret = 'MAC';
		}
		elseif (!(strpos('WIN', strtoupper($this->_response)) === false)) {
			$ret = 'WIN';
		} else {
			$ret = 'UNIX';
		}

		// Return the os type
		return $ret;
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
			JError::raiseError('35', 'JFTP::chdir: Bad response.', 'Server response:'.$this->_response.' [Expected: 250] Path sent:'.$path );
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
			JError::raiseError('35', 'JFTP::reinit: Bad response.', 'Server response:'.$this->_response.' [Expected: 220]' );
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
			JError::raiseError('35', 'JFTP::rename: Bad response.', 'Server response:'.$this->_response.' [Expected: 320] From path sent:'.$from );
			return false;
		}

		// Send rename to command to the server
		if (!$this->_putCmd('RNTO '.$to, 250)) {
			JError::raiseError('35', 'JFTP::rename: Bad response.', 'Server response:'.$this->_response.' [Expected: 250] To path sent:'.$to );
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
			JError::raiseError('35', 'JFTP::chmod: Bad response.', 'Server response:'.$this->_response.' [Expected: 200] Path sent:'.$path.' Mode sent:'.$mode );
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
				JError::raiseError('35', 'JFTP::delete: Bad response.', 'Server response:'.$this->_response.' [Expected: 250] Path sent:'.$path );
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
			JError::raiseError('35', 'JFTP::mkdir: Bad response.', 'Server response:'.$this->_response.' [Expected: 257] Path sent:'.$path );
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
			JError::raiseError('35', 'JFTP::restart: Bad response.', 'Server response:'.$this->_response.' [Expected: 350] Restart point sent:'.$point );
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
			JError::raiseError('36', 'JFTP::create: Unable to use passive mode' );
			return false;
		}

		if (!$this->_putCmd('STOR '.$path, array (150, 125))) {
			@ fclose($this->_dataconn);
			JError::raiseError('35', 'JFTP::create: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$path );
			return false;
		}

		// To create a zero byte upload close the data port connection
		fclose($this->_dataconn);

		if (!$this->_verifyResponse(226)) {
			JError::raiseError('37', 'JFTP::create: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$path );
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

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			$dot = strrpos($remote, '.') + 1;
			$ext = substr($remote, $dot);

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
		//$this->restart(0);

		// Start passive mode
		if (!$this->_passive()) {
			JError::raiseError('36', 'JFTP::read: Unable to use passive mode' );
			return false;
		}

		if (!$this->_putCmd('RETR '.$remote, array (150, 125))) {
			@ fclose($this->_dataconn);
			JError::raiseError('35', 'JFTP::read: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$remote );
			return false;
		}

		// Read data from data port connection and add to the buffer
		while (!feof($this->_dataconn)) {
			$buffer .= fread($this->_dataconn, 4096);
		}

		// Close the data port connection
		fclose($this->_dataconn);

		// Let's try to cleanup some line endings if it is ascii
		if ($mode == FTP_ASCII) {
			$buffer = preg_replace("/".CRLF."/", $this->_lineEndings[$this->_OS], $buffer);
		}

		if (!$this->_verifyResponse(226)) {
			JError::raiseError('37', 'JFTP::read: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$remote );
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

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			$dot = strrpos($remote, '.') + 1;
			$ext = substr($remote, $dot);

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
		//$this->restart(0);

		// Check to see if the local file can be opened for writing
		$fp = fopen($local, "wb");
		if (!$fp) {
			JError::raiseError('38', 'JFTP::get: Unable to open local file for writing.', 'Local path:'.$local );
			return false;
		}

		// Start passive mode
		if (!$this->_passive()) {
			JError::raiseError('36', 'JFTP::get: Unable to use passive mode' );
			return false;
		}

		if (!$this->_putCmd('RETR '.$remote, array (150, 125))) {
			@ fclose($this->_dataconn);
			JError::raiseError('35', 'JFTP::get: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$remote );
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
			JError::raiseError('37', 'JFTP::get: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$remote );
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

		// If remote file not given, use the filename of the local file in the current
		// working directory
		if ($remote == null) {
			$remote = basename($local);
		}

		// Determine file type and set transfer mode
		if ($this->_type == FTP_AUTOASCII) {
			$dot = strrpos($remote, '.') + 1;
			$ext = substr($remote, $dot);

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
		//$this->restart(0);

		// Check to see if the local file exists and open for reading if so
		if (@ file_exists($local)) {
			$fp = fopen($local, "rb");
			if (!$fp) {
				JError::raiseError('38', 'JFTP::store: Unable to open local file for reading.', 'Local path:'.$local );
				return false;
			}
		} else {
			JError::raiseError('38', 'JFTP::store: Unable to find local path', 'Local path:'.$local );
			return false;
		}

		// Start passive mode
		if (!$this->_passive()) {
			@ fclose($fp);
			JError::raiseError('36', 'JFTP::store: Unable to use passive mode' );
			return false;
		}

		// Send store command to the FTP server
		if (!$this->_putCmd('STOR '.$remote, array (150, 125))) {
			@ fclose($fp);
			@ fclose($this->_dataconn);
			JError::raiseError('35', 'JFTP::store: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$remote );
			return false;
		}

		// Do actual file transfer, read local file and write to data port connection
		while (!feof($fp)) {
			$line = fread($fp, 4096);
			do {
				if (($result = @ fwrite($this->_dataconn, $line)) === false) {
					JError::raiseError('37', 'JFTP::store: Unable to write to data port socket.' );
					return false;
				}
				$line = substr($line, $result);
			} while ($line != "");
		}

		fclose($fp);
		fclose($this->_dataconn);

		if (!$this->_verifyResponse(226)) {
			JError::raiseError('37', 'JFTP::store: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$remote );
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

		// First we need to set the transfer mode
		$this->_mode($mode);
		$this->restart(0);

		// Start passive mode
		if (!$this->_passive()) {
			JError::raiseError('36', 'JFTP::write: Unable to use passive mode' );
			return false;
		}

		// Send store command to the FTP server
		if (!$this->_putCmd('STOR '.$remote, array (150, 125))) {
			JError::raiseError('35', 'JFTP::write: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$remote );
			@ fclose($this->_dataconn);
			return false;
		}

		// Write buffer to the data connection port
		do {
			if (($result = @ fwrite($this->_dataconn, $buffer)) === false) {
				JError::raiseError('37', 'JFTP::write: Unable to write to data port socket.' );
				return false;
			}
			$buffer = substr($buffer, $result);
		} while ($buffer != "");

		// Close the data connection port [Data transfer complete]
		fclose($this->_dataconn);

		// Verify that the server recieved the transfer
		if (!$this->_verifyResponse(226)) {
			JError::raiseError('37', 'JFTP::write: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$remote );
			return false;
		}

		return true;
	}

	/**
	 * Method to list the file/folder names of the contents of a directory on the FTP server
	 *
	 * @access public
	 * @param string $path Path local file to store on the FTP server
	 * @return string Directory listing
	 */
	function nameList($path = null) {

		// Initialize variables
		$data = null;

		/*
		 * If a path exists, prepend a space
		 */
		if ($path != null) {
			$path = ' ' . $path;	
		}

		// Start passive mode
		if (!$this->_passive()) {
			JError::raiseError('36', 'JFTP::nameList: Unable to use passive mode' );
			return false;
		}

		if (!$this->_putCmd('NLST'.$path, array (150, 125))) {
			JError::raiseError('35', 'JFTP::nameList: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$path );
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
			JError::raiseError('37', 'JFTP::nameList: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$path );
			return false;
		}

		return preg_split("/[".CRLF."]+/", $data, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Method to list the contents of a directory on the FTP server
	 *
	 * @access public
	 * @param string $path Path local file to store on the FTP server
	 * @param string $type Return type [raw|all|folders|files]
	 * @param boolean $search Recursively search subdirectories
	 * @return string Directory listing
	 */
	function listDir($path = null, $type = 'all') {

		// Initialize variables
		$data = null;
		$regs = null;
		// TODO: Deal with recurse -- nightmare
		// For now we will just set it to false
		$recurse = false;

		/*
		 * If a path exists, prepend a space
		 */
		if ($path != null) {
			$path = ' ' . $path;	
		}
		
		// Determine system type for directory listing parsing
		$osType = $this->syst();

		// Start passive mode
		if (!$this->_passive()) {
			JError::raiseError('36', 'JFTP::listDir: Unable to use passive mode' );
			return false;
		}

		if (!$this->_putCmd(($recurse == true) ? 'LIST -R' : 'LIST'.$path, array (150, 125))) {
			JError::raiseError('35', 'JFTP::listDir: Bad response.', 'Server response:'.$this->_response.' [Expected: 150 or 125] Path sent:'.$path );
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
			JError::raiseError('37', 'JFTP::listDir: Transfer Failed.', 'Server response:'.$this->_response.' [Expected: 226] Path sent:'.$path );
			return false;
		}

		$contents = explode(CRLF, $data);

		/*
		 * Here is where it is going to get dirty....
		 */
		if ($osType == 'UNIX') {
			foreach ($contents as $file) {
				if (ereg("([-dl][rwxstST-]+).* ([0-9]*) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9])[ ]+(([0-9]{2}:[0-9]{2})|[0-9]{4}) (.+)", $file, $regs)) {
					$fType = (int) strpos("-dl", $regs[1] { 0 });
					//$tmp_array['line'] = $regs[0];
					$tmp_array['type'] = $fType;
					$tmp_array['rights'] = $regs[1];
					//$tmp_array['number'] = $regs[2];
					$tmp_array['user'] = $regs[3];
					$tmp_array['group'] = $regs[4];
					$tmp_array['size'] = $regs[5];
					$tmp_array['date'] = date("m-d", strtotime($regs[6]));
					$tmp_array['time'] = $regs[7];
					$tmp_array['name'] = $regs[9];
				}
				// If we just want files, do not add a folder
				if ($type == 'files' && $tmp_array['type'] == 1) {
					continue;
				}
				// If we just want folders, do not add a file
				if ($type == 'folders' && $tmp_array['type'] == 0) {
					continue;
				}
				$dir_list[] = $tmp_array;
			}
		}
		elseif ($osType == 'MAC') {
			foreach ($contents as $file) {
				if (ereg("([-dl][rwxstST-]+).* ?([0-9 ]* )?([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9])[ ]+(([0-9]{2}:[0-9]{2})|[0-9]{4}) (.+)", $file, $regs)) {
					$fType = (int) strpos("-dl", $regs[1] { 0 });
					//$tmp_array['line'] = $regs[0];
					$tmp_array['type'] = $fType;
					$tmp_array['rights'] = $regs[1];
					//$tmp_array['number'] = $regs[2];
					$tmp_array['user'] = $regs[3];
					$tmp_array['group'] = $regs[4];
					$tmp_array['size'] = $regs[5];
					$tmp_array['date'] = date("m-d", strtotime($regs[6]));
					$tmp_array['time'] = $regs[7];
					$tmp_array['name'] = $regs[9];
				}
				// If we just want files, do not add a folder
				if ($type == 'files' && $tmp_array['type'] == 1) {
					continue;
				}
				// If we just want folders, do not add a file
				if ($type == 'folders' && $tmp_array['type'] == 0) {
					continue;
				}
				$dir_list[] = $tmp_array;
			}
		} else {
			foreach ($contents as $file) {
				if (ereg("([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)", $file, $regs)) {
					// Four digit year fix
					if ($regs[3] < 70) {
						$regs[3] += 2000;
					} else {
						$regs[3] += 1900;
					}
					// File Type
					if ($regs[7] == "<DIR>") {
						$fType = 1;
					} else {
						$fType = 0;
					}
					$tmp_array['type'] = $fType;
					$tmp_array['size'] = $regs[7];
					$tmp_array['date'] = date("m-d", strtotime($regs[1].' '.$regs[2]));
					$tmp_array['time'] = $regs[4].':'.$regs[5];
					$tmp_array['name'] = $regs[8];
				}
				// If we just want files, do not add a folder
				if ($type == 'files' && $tmp_array['type'] == 1) {
					continue;
				}
				// If we just want folders, do not add a file
				if ($type == 'folders' && $tmp_array['type'] == 0) {
					continue;
				}
				$dir_list[] = $tmp_array;
			}
		}

		// One last check, do we want parsed output or raw output???
		if ($type == 'raw') {
			return $data;
		} else {
			return $dir_list;
		}
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
			JError::raiseError('31', 'JFTP::_putCmd: Not connected to the control port' );
			return false;
		}

		// Send the command to the server
		if (!fwrite($this->_conn, $cmd."\r\n")) {
			JError::raiseError('32', 'JFTP::_putCmd: Unable to send command:'.$cmd );
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

		// Initialize variables
		$parts = null;

		// Wait for a response from the server, but timeout in 5 seconds
		$time = time();
		do {
			$this->_response = fgets($this->_conn, 4096);
		} while (!preg_match("/^([0-9]{3})(-(.*".CRLF.")+\\1)? [^".CRLF."]+".CRLF."$/", $this->_response, $parts) && time() - $time < 5);

		// Separate the code from the message
		$responseCode = $parts[1];
		$responseMsg = $parts[0];

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
		$match = array();
		$parts = array();
		$errno = null;
		$err = null;

		// Make sure we have a connection to the server
		if (!is_resource($this->_conn)) {
			JError::raiseError('31', 'JFTP::_passive: Not connected to the control port' );
			return false;
		}

		// Request a passive connection - this means, we'll talk to you, you don't talk to us.
		@ fwrite($this->_conn, "PASV\r\n");
		// Wait for a response from the server, but timeout in 5 seconds
		$time = time();
		do {
			$this->_response = fgets($this->_conn, 4096);
		} while (!preg_match("/^([0-9]{3})(-(.*".CRLF.")+\\1)? [^".CRLF."]+".CRLF."$/", $this->_response, $parts) && time() - $time < 5);

		// Separate the code from the message
		$responseCode = $parts[1];
		$responseMsg = $parts[0];

		// If it's not 227, we weren't given an IP and port, which means it failed.
		if ($responseCode != '227') {
			JError::raiseError('36', 'JFTP::_passive: Unable to obtain IP and port for data transfer', 'Server response:'.$responseMsg );
			return false;
		}

		// Snatch the IP and port information, or die horribly trying...
		if (preg_match('~\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))\)~', $responseMsg, $match) == 0) {
			JError::raiseError('36', 'JFTP::_passive: IP and port for data transfer not valid', 'Server response:'.$responseMsg );
			return false;
		}

		// This is pretty simple - store it for later use ;).
		$this->_pasv = array ('ip' => $match[1].'.'.$match[2].'.'.$match[3].'.'.$match[4], 'port' => $match[5] * 256 + $match[6]);

		// Connect, assuming we've got a connection.
		$this->_dataconn =  @fsockopen($this->_pasv['ip'], $this->_pasv['port'], $errno, $err, 5);
		if (!$this->_dataconn) {
			JError::raiseError('30', 'JFTP::_passive: Could not connect to host:'.$this->_pasv['ip'].' on port:'.$this->_pasv['port'].'.  Socket error number:'.$errno.' and error message:'.$err );
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
				JError::raiseError('35', 'JFTP::_mode: Bad response.', 'Server response:'.$this->_response.' [Expected: 200] Mode sent: Binary' );
				return false;
			}
		} else {
			if (!$this->_putCmd("TYPE A", 200)) {
				JError::raiseError('35', 'JFTP::_mode: Bad response.', 'Server response:'.$this->_response.' [Expected: 200] Mode sent: Ascii' );
				return false;
			}
		}
		return true;
	}
}
?>