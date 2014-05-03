<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;

/**
 * Jokte! Update's Default View
 *
 * @package     Jokte.Administrator
 * @subpackage  com_installer
 * @since       1.2.0
 */
class JokteupdateViewDefault extends JViewLegacy
{
	/**
	 * Renders the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @since  1.2.0
	 */
	public function display($tpl=null)
	{
		// Get data from the model
		$this->state = $this->get('State');

		// Load useful classes
		$model = $this->getModel();
		$this->loadHelper('select');

		// Assign view variables
		$ftp = $model->getFTPOptions();
		$this->assign('updateInfo', $model->getUpdateInformation());
		$this->assign('updateMisc', $model->getUpdateInfoMisc());
		$this->assign('methodSelect', JokteupdateHelperSelect::getMethods($ftp['enabled']));

		// Set the toolbar information
		JToolBarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'install');

		// Add toolbar buttons
		JToolBarHelper::preferences('com_jokteupdate');

		// Load mooTools
		JHtml::_('behavior.framework', true);

		// Load our Javascript
		$document = JFactory::getDocument();
		$document->addScript('../media/com_joomlaupdate/default.js');
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);

		// Render the view
		parent::display($tpl);
	}

}
