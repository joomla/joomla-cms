<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewComponentHtml extends ConfigViewHtmlCms
{

	public $state;

	public $form;

	public $component;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Layout
	 *
	 * @return  boolean
	 *
	 */
	public function render()
	{

		$form = null;
		$component = null;

		try
		{
			$form = $this->model->getForm();
			$component	= $this->model->getComponent();
			$user = JFactory::getUser();
			$app  = JFactory::getApplication();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}

		// Bind the form to the data.
		if ($form && $component->params)
		{
			$form->bind($component->params);
		}

		$this->form = &$form;
		$this->component = &$component;

		$this->components = ConfigHelperConfig::getComponentsWithConfig();
		ConfigHelperConfig::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->currentComponent = JFactory::getApplication()->input->get('component');
		$this->return = $app->input->get('return', '', 'base64');

		$this->addToolbar();
		$app->input->set('hidemainmenu', true);

		return parent::render();

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_($this->component->option . '_configuration'), 'config.png');
		JToolbarHelper::apply('config.save.component.apply');
		JToolbarHelper::save('config.save.component');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('config.cancel.component');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
