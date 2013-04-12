<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Update View
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       2.5.4
 */
class JoomlaupdateViewUpdate extends JViewLegacy
{
	/**
	 * Renders the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 */
	public function display($tpl=null)
	{
		$password = JFactory::getApplication()->getUserState('com_joomlaupdate.password', null);
		$filesize = JFactory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
		$ajaxUrl = JURI::base().'components/com_joomlaupdate/restore.php';
		$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise';

		// Set the toolbar information
		JToolBarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'install');

		// Add toolbar buttons
		if (JFactory::getUser()->authorise('core.admin', 'com_joomlaupdate'))
		{
			JToolbarHelper::preferences('com_joomlaupdate');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');

		// Load mooTools
		JHtml::_('behavior.framework', true);

		$updateScript = <<<ENDSCRIPT
var joomlaupdate_password = '$password';
var joomlaupdate_totalsize = '$filesize';
var joomlaupdate_ajax_url = '$ajaxUrl';
var joomlaupdate_return_url = '$returnUrl';

ENDSCRIPT;

		// Load our Javascript
		$document = JFactory::getDocument();
		$document->addScript('../media/com_joomlaupdate/json2.js');
		$document->addScript('../media/com_joomlaupdate/encryption.js');
		$document->addScript('../media/com_joomlaupdate/update.js');
		JHtml::_('script', 'system/progressbar.js', true, true);
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);
		$document->addScriptDeclaration($updateScript);

		// Render the view
		parent::display($tpl);
	}

}
