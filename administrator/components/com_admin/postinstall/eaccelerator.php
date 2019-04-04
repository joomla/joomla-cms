<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for eAccelerator compatibility.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Checks if the eAccelerator caching method is enabled.
 *
 * This check should be done through the 3.x series as the issue impacts migrated sites which will
 * most often come from the previous LTS release (2.5). Remove for version 4 or when eAccelerator support is added.
 *
 * This check returns true when the eAccelerator caching method is user, meaning that the message concerning it should be displayed.
 *
 * @return  integer
 *
 * @since   3.2
 */
function admin_postinstall_eaccelerator_condition()
{
	$app = JFactory::getApplication();
	$cacheHandler = $app->get('cacheHandler', '');

	return (ucfirst($cacheHandler) == 'Eaccelerator');
}

/**
 * Disables the unsupported eAccelerator caching method, replacing it with the "file" caching method.
 *
 * @return  void
 *
 * @since   3.2
 */
function admin_postinstall_eaccelerator_action()
{
	$prev = ArrayHelper::fromObject(new JConfig);
	$data = array_merge($prev, array('cacheHandler' => 'file'));

	$config = new Registry($data);

	jimport('joomla.filesystem.path');
	jimport('joomla.filesystem.file');

	// Set the configuration file path.
	$file = JPATH_CONFIGURATION . '/configuration.php';

	// Get the new FTP credentials.
	$ftp = JClientHelper::getCredentials('ftp', true);

	// Attempt to make the file writeable if using FTP.
	if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0644'))
	{
		JError::raiseNotice(500, JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'));
	}

	// Attempt to write the configuration file as a PHP class named JConfig.
	$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

	if (!JFile::write($file, $configuration))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'), 'error');

		return;
	}

	// Attempt to make the file unwriteable if using FTP.
	if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444'))
	{
		JError::raiseNotice(500, JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
	}
}
