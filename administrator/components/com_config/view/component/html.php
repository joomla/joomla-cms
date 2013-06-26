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
 * View for the component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigViewComponentHtml extends JViewLegacy
{
	/**
	 * Display the view
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  boolean
	 * 
	 * 
	 */
	public function render($tpl = null)
	{
		$form		= $this->get('Form');
		$component	= $this->get('Component');
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Bind the form to the data.
		if ($form && $component->params)
		{
			$form->bind($component->params);
		}

		$this->form = &$form;
		$this->component = &$component;

		$this->components = ConfigHelperComponent::getComponentsWithConfig();
		ConfigHelperComponent::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->currentComponent = JFactory::getApplication()->input->get('component');
		$this->return = $app->input->get('return', '', 'base64');

		// Adding paths forcely to eliminate 's' issue
		$this->_addPath('template', $this->_basePath . '/view/component/tmpl');
		$app = JFactory::getApplication();
		$this->_addPath('template', JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_config/component');

		$this->addToolbar();
		parent::display($tpl);
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 * 
	 * @return  void
	 * 
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_($this->component->option . '_configuration'), 'config.png');
		JToolbarHelper::apply('component.apply');
		JToolbarHelper::save('component.save');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('component.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
