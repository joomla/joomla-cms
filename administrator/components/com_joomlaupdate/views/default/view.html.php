<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update's Default View
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
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
		$this->assign('methodSelect', JoomlaupdateHelperSelect::getMethods($ftp['enabled']));

		//Check update requirements
		if(version_compare($this->updateInfo['installed'], $this->updateInfo['latest'], '<') && version_compare($this->updateInfo['latest'], '3.0.0', '>')) {
			$path	= JPath::clean(JPATH_ROOT.'/requirements.xml');
			if (file_exists($path) && $this->xml_requirements = simplexml_load_file($path)){
				foreach($this->xml_requirements as $xml_data){
					if($xml_data->attributes()->release == $this->updateInfo['latest']){
						$this->assign('settings', $model->getPhpSettings($xml_data->php_settings));
						$this->assign('options', $model->getPhpOptions($xml_data->php_options));
						// Check for 3rd party extensions
						$this->assign('extensions', $model->getExtensions($this->updateInfo['latest']));
						break;
					}
				}
			}
		}


		// Set the toolbar information
		JToolBarHelper::title(JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'install');

		// Add toolbar buttons
		JToolBarHelper::preferences('com_joomlaupdate');

		// Load mooTools
		JHtml::_('behavior.framework', true);

		// Load our Javascript
		$document = JFactory::getDocument();
		$document->addScript('../media/com_joomlaupdate/default.js');
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);
		
		
		// Load Stylesheet
		JHtml::_('stylesheet', 'media/com_joomlaupdate/stylesheet.css', array(), false);
		
		
		// Render the view
		parent::display($tpl);
	}

}