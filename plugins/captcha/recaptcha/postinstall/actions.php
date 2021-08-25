<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains the functions used by the com_postinstall code to deliver
 * the necessary post-installation messages for the end of life of reCAPTCHA V1.
 */

/**
 * Checks if the plugin is enabled and reCAPTCHA V1 is being used. If true then the
 * message about reCAPTCHA v1 EOL should be displayed.
 *
 * @return  boolean
 *
 * @since  3.8.6
 */
function recaptcha_postinstall_condition()
{
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select('1')
		->from($db->qn('#__extensions'))
		->where($db->qn('name') . ' = ' . $db->q('plg_captcha_recaptcha'))
		->where($db->qn('enabled') . ' = 1')
		->where($db->qn('params') . ' LIKE ' . $db->q('%1.0%'));
	$db->setQuery($query);
	$enabled_plugins = $db->loadObjectList();

	return count($enabled_plugins) === 1;
}

/**
 * Open the reCAPTCHA plugin so that they can update the settings to V2 and new keys.
 *
 * @return  void
 *
 * @since   3.8.6
 */
function recaptcha_postinstall_action()
{
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select('extension_id')
		->from($db->qn('#__extensions'))
		->where($db->qn('name') . ' = ' . $db->q('plg_captcha_recaptcha'));
	$db->setQuery($query);
	$e_id = $db->loadResult();

	$url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $e_id;
	JFactory::getApplication()->redirect($url);
}
