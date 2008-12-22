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


// Sync Articles

$db = &JFactory::getDbo();
$db->setQuery(
	'SELECT id, title, ordering FROM #__content'
);
JAclAdmin::synchronizeAssets($db->loadObjectList(), 'com_content');

// Lets make some rules

$result = JAclAdmin::registerRule(
	// The rule type
	1,
	// The rule section
	'core',
	// The rule name
	'global.superadministrator',
	// The title of the rule
	'Global Super Administrator Permissions',
	// Applies to User Groups
	array('Super Administrator'),
	// The Actions attached to the rule
	array('core' => array(
		'acl.manage',
		'cache.manage',
		'checkin.manage',
		'config.manage',
		'installer.manage',
		'language.manage',
		'menu.manage',
		'module.manage',
		'plugin.manage',
		'template.manage',
		'user.manage',
	)),
	// Applies to Assets (Type 2 only)
	array(),
	// Applies to Asset Groups (Type 3 only)
	array()
);

$result = JAclAdmin::registerRule(
	// The rule type
	2,
	// The rule section
	'core',
	// The rule name
	'backend.login',
	// The title of the rule
	'Backend Login',
	// Applies to User Groups
	array('Manager'),
	// The Actions attached to the rule
	array('core' => array(
		'login',
	)),
	// Applies to Assets (Type 2 only)
	array('core' => array(
		1
	)),
	// Applies to Asset Groups (Type 3 only)
	array()
);

$result = JAclAdmin::registerRule(
	// The rule type
	2,
	// The rule section
	'core',
	// The rule name
	'frontend.login',
	// The title of the rule
	'Frontend Login',
	// Applies to User Groups
	array('Registered', 'Manager'),
	// The Actions attached to the rule
	array('core' => array(
		'login',
	)),
	// Applies to Assets (Type 2 only)
	array('core' => array(
		0,
	)),
	// Applies to Asset Groups (Type 3 only)
	array()
);

//
// Type 3 Rules
//

$result = JAclAdmin::registerRule(
	// The rule type
	3,
	// The rule section
	'core',
	// The rule name
	'global.public.view',
	// The title of the rule
	'View Public Content and Infrastructure',
	// Applies to User Groups
	array('Public Frontend', 'Registered', 'Author', 'Editor', 'Publisher', 'Manager', 'Administrator', 'Super Administrator'),
	// The Actions attached to the rule
	array('core' => array(
		'global.view',
	)),
	// Applies to Assets (Type 2 only)
	array(),
	// Applies to Asset Groups (Type 3 only)
	array(
		0,
	)
);

$result = JAclAdmin::registerRule(
	// The rule type
	3,
	// The rule section
	'core',
	// The rule name
	'global.registered.view',
	// The title of the rule
	'View Registered Content and Infrastructure',
	// Applies to User Groups
	array('Registered', 'Author', 'Editor', 'Publisher', 'Manager', 'Administrator', 'Super Administrator'),
	// The Actions attached to the rule
	array('core' => array(
		'global.view',
	)),
	// Applies to Assets (Type 2 only)
	array(),
	// Applies to Asset Groups (Type 3 only)
	array(
		1,
	)
);


