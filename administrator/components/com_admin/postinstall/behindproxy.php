<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Notifies users of the new Behind Load Balancer option in Global Config, if we detect they might be behind a proxy
 *
 * @return  boolean
 *
 * @since   3.9.26
 */
function admin_postinstall_behindproxy_condition()
{
	$app = JFactory::getApplication();

	if ($app->get('behind_loadbalancer', '0'))
	{
		return false;
	}

	if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		return true;
	}

	if (array_key_exists('HTTP_CLIENT_IP', $_SERVER) && !empty($_SERVER['HTTP_CLIENT_IP']))
	{
		return true;
	}

	return false;
}


/**
 * Enables the Behind Load Balancer setting in Global Configuration
 *
 * @return  void
 *
 * @since   3.9.26
 */
function behindproxy_postinstall_action()
{
	$prev = ArrayHelper::fromObject(new JConfig);
	$data = array_merge($prev, array('behind_loadbalancer' => '1'));

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

	if (!File::write($file, $configuration))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'), 'error');

		return;
	}

	// Attempt to make the file unwriteable if NOT using FTP.
	if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0444'))
	{
		JError::raiseNotice(500, JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'));
	}
}
