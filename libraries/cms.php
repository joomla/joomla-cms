<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', __DIR__);
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('JLoader'))
{
	throw new RuntimeException('Joomla Platform not loaded.');
}

// Register the library base path for CMS libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

// Register a handler for uncaught exceptions that shows a pretty error page when possible
set_exception_handler(array('JErrorPage', 'render'));

// Define the Joomla version if not already defined.
if (!defined('JVERSION'))
{
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(array('logger' => 'messagequeue'), JLog::ALL, array('jerror'));
}

// Register classes where the names have been changed to fit the autoloader rules
// @deprecated  4.0
JLoader::register('JToolBar', JPATH_PLATFORM . '/cms/toolbar/toolbar.php');
JLoader::register('JButton',  JPATH_PLATFORM . '/cms/toolbar/button.php');
JLoader::register('JInstallerComponent',  JPATH_PLATFORM . '/cms/installer/adapter/component.php');
JLoader::register('JInstallerFile',  JPATH_PLATFORM . '/cms/installer/adapter/file.php');
JLoader::register('JInstallerLanguage',  JPATH_PLATFORM . '/cms/installer/adapter/language.php');
JLoader::register('JInstallerLibrary',  JPATH_PLATFORM . '/cms/installer/adapter/library.php');
JLoader::register('JInstallerModule',  JPATH_PLATFORM . '/cms/installer/adapter/module.php');
JLoader::register('JInstallerPackage',  JPATH_PLATFORM . '/cms/installer/adapter/package.php');
JLoader::register('JInstallerPlugin',  JPATH_PLATFORM . '/cms/installer/adapter/plugin.php');
JLoader::register('JInstallerTemplate',  JPATH_PLATFORM . '/cms/installer/adapter/template.php');
JLoader::register('JExtension',  JPATH_PLATFORM . '/cms/installer/extension.php');

// Register Observers:
// Add Tags to Content, Contact, NewsFeeds, WebLinks and Categories: (this is the only link between them here!):
JObserverMapper::addObserverClassToClass('JTableObserverTags', 'JTableContent', array('typeAlias' => 'com_content.article'));
JObserverMapper::addObserverClassToClass('JTableObserverTags', 'ContactTableContact', array('typeAlias' => 'com_contact.contact'));
JObserverMapper::addObserverClassToClass('JTableObserverTags', 'NewsfeedsTableNewsfeed', array('typeAlias' => 'com_newsfeeds.newsfeed'));
JObserverMapper::addObserverClassToClass('JTableObserverTags', 'WeblinksTableWeblink', array('typeAlias' => 'com_weblinks.weblink'));
JObserverMapper::addObserverClassToClass('JTableObserverTags', 'JTableCategory', array('typeAlias' => '{extension}.category'));

// Register Observers for Version History
JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'ContactTableContact', array('typeAlias' => 'com_contact.contact'));
JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'JTableContent', array('typeAlias' => 'com_content.article'));
JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'JTableCategory', array('typeAlias' => '{extension}.category'));
JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'NewsfeedsTableNewsfeed', array('typeAlias' => 'com_newsfeeds.newsfeed'));
JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'WeblinksTableWeblink', array('typeAlias' => 'com_weblinks.weblink'));

require_once JPATH_LIBRARIES . '/framework/aliases.php';
