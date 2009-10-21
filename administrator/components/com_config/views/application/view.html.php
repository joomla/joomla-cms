<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigViewApplication extends JView
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

		// Get other component parameters.
		$table = JTable::getInstance('component');

		// Get the params for com_users.
		$table->loadByOption('com_users');
		$usersParams = new JParameter($table->params, JPATH_ADMINISTRATOR.'/components/com_users/config.xml');

		// Get the params for com_media.
		$table->loadByOption('com_media');
		$mediaParams = new JParameter($table->params, JPATH_ADMINISTRATOR.'/components/com_media/config.xml');

		// Load settings for the FTP layer.
		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('form',	$form);
		$this->assignRef('data',	$data);
		$this->assignRef('ftp',		$ftp);
		$this->assignRef('usersParams', $usersParams);
		$this->assignRef('mediaParams', $mediaParams);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Global Configuration'), 'config.png');
		JToolBarHelper::save('application.save');
		JToolBarHelper::apply('application.apply');
		JToolBarHelper::cancel('application.cancel', 'Close');
		JToolBarHelper::help('screen.config');
	}

	protected function warningIcon()
	{
		$tip = '<img src="'.JURI::root().'includes/js/ThemeOffice/warning.png" border="0"  alt="" />';
		return $tip;
	}
}
