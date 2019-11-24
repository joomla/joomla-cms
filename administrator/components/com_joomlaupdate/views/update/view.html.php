<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Update View
 *
 * @since  2.5.4
 */
class JoomlaupdateViewUpdate extends JViewLegacy
{
	/**
	 * Renders the view.
	 *
	 * @param   string  $tpl  Template name.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Set the toolbar information.
		JToolbarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'loop install');

		// Import com_login's model
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_login/models', 'LoginModel');

		// Render the view.
		parent::display($tpl);
	}
}
