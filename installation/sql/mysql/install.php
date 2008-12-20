<?php

/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Man, there must be an easier way to do this!
// JFactory::getDbo is used by Acl API and the DB config was not set
// hence the following "stuff"
$config =& JFactory::getConfig();
$config->setValue('config.dbtype', JArrayHelper::getValue($vars, 'DBtype', 'mysql'));
$config->setValue('config.host', JArrayHelper::getValue($vars, 'DBhostname', ''));
$config->setValue('config.user', JArrayHelper::getValue($vars, 'DBuserName', ''));
$config->setValue('config.password', JArrayHelper::getValue($vars, 'DBpassword', ''));
$config->setValue('config.db', JArrayHelper::getValue($vars, 'DBname', ''));
$config->setValue('config.dbprefix', JArrayHelper::getValue($vars, 'DBPrefix', ''));

// Now, for the ACL magic

jimport('joomla.acl.acladmin');

// Lets make some rules

$result = JAclAdmin::registerRule(
	// The rule type
	1,
	// The rule section
	'core',
	// The rule name
	'global.administrator',
	// The title of the rule
	'Global Administrator Permissions',
	// Applies to User Groups
	array('Administrator'),
	// The Actions attached to the rule
	array('core' => array(
		'module.manage',
		'template.manage',
	)),
	// Applies to Assets (Type 2 only)
	array(),
	// Applies to Asset Groups (Type 3 only)
	array()
);
