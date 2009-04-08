<?php

/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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

