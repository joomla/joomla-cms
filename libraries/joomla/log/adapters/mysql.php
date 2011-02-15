<?php
defined('_JEXEC') or die();

/**
 * Joomla! MySQL Database Log class
 *
 * This class is designed to output logs to a specific MySQL database
 * table. Fields in this table are based on the SysLog style of
 * log output. This is designed to allow quick and easy searching.
 *
 * @package Joomla.Framework
 * @subpackage Log
 * @since 1.7
 */
 
class JLogMySQL extends JLogFormat {
	
	function JLogMySQL() {
		
	}
	
	function &getInstance() { 
		static $instances;
		$sig = md5('jlogmysql');
		if(!is_object($instances[$sig])) {
			$instances[$sig] = new JLogMySQL();
		}
		return $instances[$sig];
	}

/*	function &getInstance() {
		return new JLogMySQL();
	}*/
/*	function &getInstance() { 
		static $instances;
		$sig = md5('jlogmysql');
		if(!is_object($instances[$sig])) {
			$instances = new JLogMySQL();
		}
		return $instances[$sig];
	}*/

	function addLogEntry($entry) {
		$db =& JFactory::getDBO();
		$query = 'INSERT INTO #__log_entries VALUES (0,"'.$entry->application.'", "'.$entry->type.'", "'.$entry->priority.'", "'. $entry->entrydate .'", "'.$entry->message.'")';
		$db->setQuery($query);
		$db->Query();
	}
}