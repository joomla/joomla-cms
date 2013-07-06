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
class ConfigViewApplicationHtml extends JViewCms
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
			$app = JFactory::getApplication();

		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
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

		$this->components = ConfigHelperComponent::getComponentsWithConfig();
		ConfigHelperComponent::loadLanguageForComponents($this->components);

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
		JToolbarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'config.png');
		JToolbarHelper::apply('application.apply');
		JToolbarHelper::save('application.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('application.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
