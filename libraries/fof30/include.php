<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

defined('_JEXEC') or die;

if (!class_exists('FOF30\\Autoloader\\Autoloader'))
{
	// Register utility functions
	require_once __DIR__ . '/Utils/helpers.php';
	// Register the FOF autoloader
	require_once __DIR__ . '/Autoloader/Autoloader.php';
}

if (!defined('FOF30_INCLUDED'))
{
	define('FOF30_INCLUDED', '3.6.2');

	JFactory::getLanguage()->load('lib_fof30', JPATH_SITE, 'en-GB', true);
	JFactory::getLanguage()->load('lib_fof30', JPATH_SITE, null, true);

	// Register a debug log
	if (defined('JDEBUG') && JDEBUG && class_exists('JLog'))
	{
		\JLog::addLogger(array('text_file' => 'fof.log.php'), \JLog::ALL, array('fof'));
	}
}
