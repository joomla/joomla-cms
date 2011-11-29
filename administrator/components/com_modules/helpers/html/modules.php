<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
abstract class JHtmlModules
{
	/**
	 * @param	int $clientId	The client id
	 * @param	string $state 	The state of the template
	 */
	static public function templates($clientId = 0, $state = '')
	{
		$templates = ModulesHelper::getTemplates($clientId, $state);
		foreach ($templates as $template) {
			$options[]	= JHtml::_('select.option', $template->element, $template->name);
		}
		return $options;
	}
	/**
	 */
	static public function types()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'user', 'COM_MODULES_OPTION_POSITION_USER_DEFINED');
		$options[] = JHtml::_('select.option', 'template', 'COM_MODULES_OPTION_POSITION_TEMPLATE_DEFINED');
		return $options;
	}

	/**
	 */
	static public function templateStates()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '1', 'JENABLED');
		$options[] = JHtml::_('select.option', '0', 'JDISABLED');
		return $options;
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param   integer       $value			The state value.
	 * @param   integer       $i				The row index
	 * @param   boolean       $enabled			An optional setting for access control on the action.
	 * @param   string        $checkbox			An optional prefix for checkboxes.
	 *
	 * @return  string        The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since   1.7.1
	 */
	public static function state($value, $i, $enabled = true, $checkbox = 'cb')
	{
		$states	= array(
			1	=> array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				'COM_MODULES_HTML_UNPUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_ENABLED',
				true,
				'publish',
				'publish'
			),
			0	=> array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				'COM_MODULES_HTML_PUBLISH_ENABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_ENABLED',
				true,
				'unpublish',
				'unpublish'
			),
			-1	=> array(
				'unpublish',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				'COM_MODULES_HTML_UNPUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_PUBLISHED_DISABLED',
				true,
				'warning',
				'warning'
			),
			-2	=> array(
				'publish',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				'COM_MODULES_HTML_PUBLISH_DISABLED',
				'COM_MODULES_EXTENSION_UNPUBLISHED_DISABLED',
				true,
				'unpublish',
				'unpublish'
			),
		);

		return JHtml::_('jgrid.state', $states, $value, $i, 'modules.', $enabled, true, $checkbox);
	}
}
