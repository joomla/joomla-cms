<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Default View
 *
 * @since       2.5.4
 */
class JoomlaupdateViewDefault extends JViewLegacy
{
	/**
	 * Renders the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @since  2.5.4
	 */
	public function display($tpl = null)
	{
		// Get data from the model.
		$this->state = $this->get('State');

		// Load useful classes.
		$model = $this->getModel();
		$this->loadHelper('select');

		// Assign view variables.
		$ftp = $model->getFTPOptions();
		$this->assign('updateInfo', $model->getUpdateInformation());
		$this->assign('methodSelect', JoomlaupdateHelperSelect::getMethods($ftp['enabled']));

		// Set the toolbar information.
		JToolbarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'arrow-up-2 install');
		JToolbarHelper::custom('update.purge', 'purge', 'purge', 'JTOOLBAR_PURGE_CACHE', false, false);

		// Add toolbar buttons.
		if (JFactory::getUser()->authorise('core.admin', 'com_joomlaupdate'))
		{
			JToolbarHelper::preferences('com_joomlaupdate');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');

		// Load mooTools.
		JHtml::_('behavior.framework', true);

		// Include jQuery.
		JHtml::_('jquery.framework');

		// Load our Javascript.
		$document = JFactory::getDocument();
		$document->addScript('../media/com_joomlaupdate/default.js');
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);

		if (!is_null($this->updateInfo['object']))
		{
			// Show the message if a update is found.
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATE_NOTICE'), 'notice');
		}

		// Render the view.
		parent::display($tpl);
	}
}
