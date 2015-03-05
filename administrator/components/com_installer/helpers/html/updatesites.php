<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer HTML class.
 *
 * @since  3.4.1
 */
abstract class InstallerHtmlUpdatesites
{
	/**
	 * Returns a published state on a grid.
	 *
	 * @param   integer  $value     The state value.
	 * @param   integer  $i         The row index.
	 * @param   boolean  $enabled   An optional setting for access control on the action.
	 * @param   string   $checkbox  An optional prefix for checkboxes.
	 *
	 * @return  string   The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since   3.4.1
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states	= array(
			2 => array(
				'',
				'COM_INSTALLER_UPDATESITE_PROTECTED',
				'',
				'COM_INSTALLER_UPDATESITE_PROTECTED',
				true,
				'protected',
				'protected',
			),
			1 => array(
				'unpublish',
				'COM_INSTALLER_UPDATESITE_ENABLED',
				'COM_INSTALLER_UPDATESITE_DISABLE',
				'COM_INSTALLER_UPDATESITE_ENABLED',
				true,
				'publish',
				'publish',
			),
			0 => array(
				'publish',
				'COM_INSTALLER_UPDATESITE_DISABLED',
				'COM_INSTALLER_UPDATESITE_ENABLE',
				'COM_INSTALLER_UPDATESITE_DISABLED',
				true,
				'unpublish',
				'unpublish',
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'updatesites.', $enabled, true, $checkbox);
	}
}
