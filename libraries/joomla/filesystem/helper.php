<?php
/**
 * File system helper
 * 
 * Holds support functions for the filesystem, particularly the stream 
 * 
 * PHP5
 *  
 * Created on Sep 22, 2008
 * 
 * @package Joomla
 * @subpackage Filesystem
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 OpenSourceMatters 
 * @version SVN: $Id:$    
 */
 
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class JFilesystemHelper {
	// ----------------------------
	// Support Functions; should probably live in a helper?
	// ----------------------------
	
	/**
	 * Remote file size function for streams that don't support it
	 * @see http://www.php.net/manual/en/function.filesize.php#71098
	 */
    function remotefsize($url) {
        $sch = parse_url($url, PHP_URL_SCHEME);
        if (($sch != "http") && ($sch != "https") && ($sch != "ftp") && ($sch != "ftps")) {
            return false;
        }
        if (($sch == "http") || ($sch == "https")) {
            $headers = get_headers($url, 1);
            if ((!array_key_exists("Content-Length", $headers))) { return false; }
            return $headers["Content-Length"];
        }
        if (($sch == "ftp") || ($sch == "ftps")) {
            $server = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT);
            $path = parse_url($url, PHP_URL_PATH);
            $user = parse_url($url, PHP_URL_USER);
            $pass = parse_url($url, PHP_URL_PASS);
            if ((!$server) || (!$path)) { return false; }
            if (!$port) { $port = 21; }
            if (!$user) { $user = "anonymous"; }
            if (!$pass) { $pass = ""; }
            switch ($sch) {
                case "ftp":
                    $ftpid = ftp_connect($server, $port);
                    break;
                case "ftps":
                    $ftpid = ftp_ssl_connect($server, $port);
                    break;
            }
            if (!$ftpid) { return false; }
            $login = ftp_login($ftpid, $user, $pass);
            if (!$login) { return false; }
            $ftpsize = ftp_size($ftpid, $path);
            ftp_close($ftpid);
            if ($ftpsize == -1) { return false; }
            return $ftpsize;
        }
    }
    
	/**
	 * Quick FTP chmod
	 * @see http://www.php.net/manual/en/function.ftp-chmod.php
	 */
    function ftpChmod($url, $mode) {
        $sch = parse_url($url, PHP_URL_SCHEME);
        if (($sch != "ftp") && ($sch != "ftps")) {
            return false;
        }
        $server = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $path = parse_url($url, PHP_URL_PATH);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);
        if ((!$server) || (!$path)) { return false; }
        if (!$port) { $port = 21; }
        if (!$user) { $user = "anonymous"; }
        if (!$pass) { $pass = ""; }
        switch ($sch) {
            case "ftp":
                $ftpid = ftp_connect($server, $port);
                break;
            case "ftps":
                $ftpid = ftp_ssl_connect($server, $port);
                break;
        }
        if (!$ftpid) { return false; }
        $login = ftp_login($ftpid, $user, $pass);
        if (!$login) { return false; }
        $res = ftp_chmod($ftpid, $mode, $path);
        ftp_close($ftpid);
        return $res;
    }    
    
    /**
     * Modes that require a write operation
     */
    static function getWriteModes() {
		return Array('w','w+','a','a+','r+','x','x+');
    }	

	// ----------------------------
	// Stream and Filter Support Operations
	// ----------------------------
	
	/**
	 * Returns the supported streams, in addition to direct file access
	 * Also includes Joomla! streams as well as PHP streams
	 * @return Array Streams
	 */
	function getSupported() {
		// really quite cool what php can do with arrays when you let it...
		static $streams;
		if(!$streams) $streams = array_merge(stream_get_wrappers(), JFilesystemHelper::getJStreams());
		return $streams;
	}
	
	/**
	 * Returns a list of transports
	 */
	function getTransports() {
		// is this overkill?
		return stream_get_transports();
	}
	
	/**
	 * Returns a list of filters
	 */
	function getFilters() {
		// note: this will look like the getSupported() function with J! filters
		// TODO: add user space filter loading like user space stream loading
		return stream_get_filters();
	}
	
	/**
	 * Returns a list of J! streams
	 */
	function getJStreams() {
		static $streams;
		if(!$streams) $streams = array_map(array('JFile','stripExt'),JFolder::files(dirname(__FILE__).DS.'streams','.php'));
		return $streams;
	}
	
	function isJoomlaStream($streamname) {
		return in_array($streamname, JFilesystemHelper::getJStreams());
	}
}