<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ConfigViewApplicationHtml extends JViewItem
{	
	public function render($tpl = null)
	{
		// Get the params for com_users.
		$usersParams = JComponentHelper::getParams('com_users');

		// Get the params for com_media.
		$mediaParams = JComponentHelper::getParams('com_media');

		// Load settings for the FTP layer.
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');

		$this->ftp = &$ftp;
		$this->usersParams = &$usersParams;
		$this->mediaParams = &$mediaParams;

		/** @var ConfigModelApplication $model */
		$model = $this->getModel();
		$this->components = $model->getList();
		ConfigHelperConfig::loadLanguageForComponents($this->components);

		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		$this->addToolbar();

		return parent::render($tpl);
	}

	/**
	 * Work around for the layouts
	 * @param string $name
	 * @param string $fieldName
	 * @param string $description
	 *
	 * @return stdClass
	 */
	protected function getDisplayData($name, $fieldName, $description = null)
	{
		$displayData = new stdClass();
		$displayData->form = $this->form;
		$displayData->name = JText::_($name);
		$displayData->fieldsname = $fieldName;

		if(!empty($description))
		{
			$displayData->description = JText::_($description);
		}

		return $displayData;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'equalizer config');
		JToolbarHelper::apply('store');
		JToolbarHelper::save('store.close');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}

	/**
	 * This is a special case, because we are not using the DB, so overriding here allows us
	 * to skip the user session security check.
	 * @param $model
	 */
	protected function prepareForm($model)
	{
		$this->form->bind($this->item);
	}

	public function canView()
	{
		return true;
	}
}
