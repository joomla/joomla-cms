<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class ConfigViewApplicationHtml extends ConfigViewCmsHtml
{
	public $state;

	public $form;

	public $data;

	/**
	 * Method to display the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		$form = null;
		$data = null;

		try
		{
			// Load Form and Data
			$form = $this->model->getForm();
			$data = $this->model->getData();
			$user = JFactory::getUser();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Bind data
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

		$this->components = ConfigHelperConfig::getComponentsWithConfig();
		ConfigHelperConfig::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$this->addToolbar();

		return parent::render();
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
		JToolbarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'equalizer config');
		JToolbarHelper::apply('config.save.application.apply');
		JToolbarHelper::save('config.save.application.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('config.cancel.application');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
