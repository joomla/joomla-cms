<?php
/**
 * Inspector classes for the JLog package.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Log
 */
class JLogInspector extends JLog
{
	public $configurations;
	public $loggers;
	public $lookup;

	public function __construct()
	{
		return parent::__construct();
	}

	public function addLogEntry(JLogEntry $entry)
	{
		return parent::addLogEntry($entry);
	}

	public function findLoggers($priority, $category)
	{
		return parent::findLoggers($priority, $category);
	}
}