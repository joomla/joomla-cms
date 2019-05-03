<?php
/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid entry point.
const _JEXEC = 1;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\Console\Application;
use Joomla\Database\Command\ExportCommand;
use Joomla\Database\Command\ImportCommand;
use Joomla\Database\DatabaseInterface;

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

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

$application = new Application;
$db = Factory::getDbo();
$application->addCommand(new ExportCommand($db));
$application->addCommand(new ImportCommand($db));
$application->execute();
