<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Inspector class for the JDaemon library.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application
 */
class JDaemonInspector extends JDaemon
{
	public $exiting;
	public $config;
	public $running;
	public $processId;
	public $inputs;
	public static $signals;

	public function __construct($config = array())
	{
		return parent::__construct($config);
	}

	public function gc()
	{
		return parent::gc();
	}

	public function daemonize()
	{
		return parent::daemonize();
	}

	public function setupSignalHandlers()
	{
		return parent::setupSignalHandlers();
	}

	public function fork()
	{
		return parent::fork();
	}

	public function writeProcessIdFile()
	{
		return parent::writeProcessIdFile();
	}

	public function shutdown($restart = false)
	{
		return parent::shutdown($restart);
	}

	public function loadConfiguration($file)
	{
		return parent::loadConfiguration($file);
	}
}