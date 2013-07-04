<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once dirname(dirname(__DIR__)) . '/helper/component.php';

/**
 * View for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewApplicationHtml extends JViewLegacy
{
	public $state;

	public $form;

	public $data;

	/**
	 * Method to display the view.
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  void
	 * 
	 */
	public function render($tpl = null)
	{
		$form	= $this->get('Form');
		$data	= $this->get('Data');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check for model errors.
		if ($errors = $this->get('Errors'))
		{
			$app->enqueueMessage(implode('<br />', $errors), 'error');

			return;

		}

		// Bind the form to the data.
		if ($form && $data)
		{
			$form->bind($data);
		}

		// Get the params for com_users.
		$usersParams = JComponentHelper::getParams('com_users');

		// Get the params for com_media.
		$mediaParams = JComponentHelper::getParams('com_media');

		// Load settings for the FTP layer.
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');

		$this->form = &$form;
		$this->data = &$data;
		$this->ftp = &$ftp;
		$this->usersParams = &$usersParams;
		$this->mediaParams = &$mediaParams;

		$this->components = ConfigHelperComponent::getComponentsWithConfig();
		ConfigHelperComponent::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$this->_addPath('template', JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_config/application');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * 
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'config.png');
		JToolbarHelper::apply('application.apply');
		JToolbarHelper::save('application.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('application.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
