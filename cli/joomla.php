<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Check for presence of vendor dependencies not included in the git repository
if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor'))
{
	echo 'It looks like you are trying to run Joomla! from our git repository.' . PHP_EOL;
	echo 'To do so requires you complete a couple of extra steps first.' . PHP_EOL;
	echo 'Please see https://docs.joomla.org/Special:MyLanguage/J4.x:Setting_Up_Your_Local_Environment for further details.' . PHP_EOL;

	exit;
}

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

$app = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Console\Application::class);
\Joomla\CMS\Factory::$application = $app;
$app->execute();
