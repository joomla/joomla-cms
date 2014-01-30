<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
class ConfigViewComponentHtml extends ConfigViewCmsHtml
{
	public $state;

	public $form;

	public $component;

	/**
	 * Display the view
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 *
	 */
	public function render()
	{
		$form = null;
		$component = null;

		try
		{
			$form = $this->model->getForm();
			$component = $this->model->getComponent();
			$user = JFactory::getUser();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
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
		$this->return = JFactory::getApplication()->input->get('return', '', 'base64');

		$this->addToolbar();
		JFactory::getApplication()->input->set('hidemainmenu', true);

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
		JToolbarHelper::title(JText::_($this->component->option . '_configuration'), 'equalizer config');
		JToolbarHelper::apply('config.save.component.apply');
		JToolbarHelper::save('config.save.component.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('config.cancel.component');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_' . $this->currentComponent . '_OPTIONS');
	}
}
