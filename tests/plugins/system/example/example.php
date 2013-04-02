<?php
/**
 * @package		Joomla
 * @subpackage	system
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This example system plugin makes a log entry for each of the system triggers.  PN 28-Mar-11
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Example system Plugin
 *
 * @package		Joomla
 * @subpackage	system
 * @since		1.5
 */
class plgSystemExample extends JPlugin
{
	var $_cache = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param	array	$config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onAfterInitialise()
	{
		$this->LogIt
		(
			'onAfterInitialise',
			'After framework load and application initialise.'
		);
	}

	public function onAfterRoute()
	{
		$this->LogIt
		(
			'onAfterRoute',
			'After the framework load, application initialised & route of client request.'
		);
	}

	public function onAfterDispatch()
	{
		$this->LogIt
		(
			'onAfterDispatch',
			'After the framework has dispatched the application.'
		);
	}

	public function onBeforeRender()
	{
		$this->LogIt
		(
			'onBeforeRender',
			'Before the framework renders the application.'
		);
	}

	public function onBeforeCompileHead()
	{
		$this->LogIt
		(
			'onBeforeCompileHead',
			'Before the framework creates the head section of the document.'
		);
	}

	public function onAfterRender()
	{
		$this->LogIt
		(
			'onAfterRender',
			'After the framework has rendered the application.'
		);
	}

	function LogIt ($status, $comment)
	{
		jimport('joomla.error.log');
		$log = JLog::getInstance('plugin_system_example_log.php');
		$log->addEntry(array('status' => $status, 'comment' => $comment));
	}
}
