<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for eAccelerator compatibility.
 */

defined('_JEXEC') or die;

/**
 * Checks if the plugin is enabled. If not it returns true, meaning that the
 * message concerning the "Install from Web" plugin should be displayed.
 *
 * @return  boolean
 *
 * @since   3.2
 */
function admin_postinstall_installfromweb_condition()
{
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__extensions'))
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('enabled') . ' = ' . $db->q('1'))
		->where($db->qn('name') . ' = ' . $db->q('plg_installer_webinstaller'))
		->where($db->qn('element') . ' = ' . $db->q('webinstaller'))
		->where($db->qn('folder') . ' = ' . $db->q('installer'));
	$db->setQuery($query);
	$enabled_plugins = $db->loadObjectList();

	return count($enabled_plugins) == 0;
}

/**
 * Redirects to installer to install the "Install from Web" plugin
 *
 * @return  void
 *
 * @since   3.2
 */
function admin_postinstall_installfromweb_action()
{
	$app = JFactory::getApplication();
	// Fake the form so a token is present in POST
	
	$app->input->set('installtype', 'url');
	$app->input->set('install_url', 'http://appscdn.joomla.org/webapps/jedapps/webinstaller.xml');
	// Making sure the request comes from a valid page
	if (JSession::checkToken('GET'))
	{
		$app->input->post->set(JSession::getFormToken(), 1);
	}

	// Load controller, model and language file from com_install
	require_once(JPATH_ADMINISTRATOR.'/components/com_installer/controllers/install.php');
	require_once(JPATH_ADMINISTRATOR.'/components/com_installer/models/install.php');
	$lang = JFactory::getLanguage();
	$lang->load('com_installer');
	$installer = new InstallerControllerInstall();
	$installer->install();

	// Redirect the user to the installer
	$app->redirect('index.php?option=com_installer');
}
