<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigViewApplication extends JViewLegacy
{
	public $state;
	public $form;
	public $data;

	/**
	 * Method to display the view.
	 */
	public function display($tpl = null)
	{
		$form	= $this->get('Form');
		$data	= $this->get('Data');

		// Check for model errors.
		if ($errors = $this->get('Errors')) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Bind the form to the data.
		if ($form && $data) {
			$form->bind($data);
		}

		// Get the params for com_users.
		$usersParams = JComponentHelper::getParams('com_users');

		// Get the params for com_media.
		$mediaParams = JComponentHelper::getParams('com_media');

		// Load settings for the FTP layer.
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('form',	$form);
		$this->assignRef('data',	$data);
		$this->assignRef('ftp',		$ftp);
		$this->assignRef('usersParams', $usersParams);
		$this->assignRef('mediaParams', $mediaParams);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'config.png');
		JToolBarHelper::apply('application.apply');
		JToolBarHelper::save('application.save');
		JToolBarHelper::divider();
		JToolBarHelper::cancel('application.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
