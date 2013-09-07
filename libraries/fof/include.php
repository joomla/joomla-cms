<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  include
 *  @copyright   Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 *
 *  Initializes FOF
 */

defined('_JEXEC') or die();

if (!defined('FOF_INCLUDED'))
{
    define('FOF_INCLUDED', '2.1.rc2');

	// Register a debug log
	if (defined('JDEBUG') && JDEBUG)
	{
		JLog::addLogger(array('text_file' => 'fof.log.php'), JLog::ALL, array('fof'));
	}

	// Register the FOF autoloader
    require_once __DIR__ . '/autoloader/fof.php';
	FOFAutoloaderFof::init();
}