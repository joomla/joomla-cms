<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Update View
 *
 * @since  3.6.0
 */
class JoomlaupdateViewUpload extends JViewLegacy
{
	/**
	 * Renders the view.
	 *
	 * @param   string  $tpl  Template name.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function display($tpl = null)
	{
		// Set the toolbar information.
		JToolbarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'loop install');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');

		// Load com_installer's language
		$language = JFactory::getLanguage();
		$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
		$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

		// Import com_login's model
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_login/models', 'LoginModel');

		// Render the view.
		parent::display($tpl);
	}
}
