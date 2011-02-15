<?php

defined('_JEXEC') or die();

/**
 * Joomla! Logging Format Base Class
 * 
 * This class is used to be the basis of logging format classes
 * to allow for defined functions to exist regardless of the
 * child class
 *
 * @package Joomla.Framework
 * @ssubpackage Log
 * @since 1.7
 */
class JLogFormat extends JObject {
	/**
	 * Defines the fields available for listing
	 * @return array an array of fields available for searching
	 */
	function getFields() {
		return Array ();
	}

	/**
	 * Determines if the format can handle search operations
	 * @return bool
	 */
	function isSearchable() {
		return false;
	}

	/**
	 * Determines if the format is writeable
	 * (e.g. a file may not be writable but is readable)
	 * @return bool status of write ability
	 */
	function isWriteable() {
		return false;
	}

	/**
	 * Determines if the format is readable
	 * A format may not be readable (e.g. syslog)
	 * @return bool status of read ability
	 */
	function isReadable() {
		return false;
	}

	/**
	 * Determines if the format can return an array of JLogEntries
	 * @return bool status of parseability
	 */
	function isParseable() {
		return false;
	}

	/**
	 * Adds a log entry to the format
	 */
	function addLogEntry($logentry) {

	}

	/**
	 * Set options
	 * @param array options Set internal options
	 * @abstract
	 */
	function setOptions($options) {

	}

}
