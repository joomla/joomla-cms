<?php

defined('_JEXEC') or die();

/**
 * Joomla! Log Entry class
 *
 * This class is designed to hold log entries for either writing
 * to an engine, or for supported engines, retrieving lists 
 * and building in memory (PHP based) search operations
 * 
 * @author Samuel Moffatt <sam.moffatt@joomla.org>
 * @package Joomla.Framework
 * @subpackage Log
 * @since 1.7
 */
class JLogEntry extends JObject {
	/** @var int logid Log Entry ID */
	public $logid = 0;
	/** @var string application Application responsible for log entry */
	public $application = '';
	/** @var string type Type of Entry */
	public $type = '';
	/** @var string priority Priority of entry ('panic', 'emerg', 'alert', 'crit', 'err', 'error', 'warn', 'warning', 'notice', 'info', 'debug', 'none') */
	public $priority = 'info';
	/** @var date entrydate Date of Entry */
	public $entrydate = '0000-00-00 00:00:00'; // YYYY-MM-DD HH:MM:SS
	/** @var string message Message to be logged */
	public $message = '';

	/**
	 * Constructor
	 * @param $application Application identifier (extension unique element)
	 * @param $type Type of entry
	 * @param $priority Syslog style priority of entry (e.g. 'panic', 'emerg', 'alert', 'crit', 'err', 'error', 'warn', 'warning', 'notice', 'info', 'debug', 'none')
	 * @param $message Contents of message
	 * @param $entrydate Date of entry (defaults to now if not specified or blank) 
	 * @param $logid Unique log identifier (user asssigned)
	 */
	function __construct($application='',$type='',$priority = 'info', $message = '', $entrydate = null, $logid = 0) {
		$this->application = $application;
		$this->type = $type;
		$this->priority = $priority;
		$this->message = $message;
		if(empty($entrydate)) {
			$this->entrydate = JFactory::getDate()->toMySQL();
		}
		$this->logid = $logid;
	}
}
