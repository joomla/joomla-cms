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
 * @package     Joomla.Administrator
 * @subpackage  com_config
 */
class ConfigViewComponent extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$form		= $this->get('Form');
		$component	= $this->get('Component');
		$user = JFactory::getUser();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the form to the data.
		if ($form && $component->params) {
			$form->bind($component->params);
		}

		$this->assignRef('form',		$form);
		$this->assignRef('component',	$component);

		$this->components = ConfigHelperComponent::getComponentsWithConfig();
		ConfigHelperComponent::loadLanguageForComponents($this->components);

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->currentComponent = JFactory::getApplication()->input->get('component');

		$this->addToolbar();
		parent::display($tpl);
		JRequest::setVar('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'config.png');
		JToolBarHelper::apply('component.apply');
		JToolBarHelper::save('component.save');
		JToolBarHelper::divider();
		JToolBarHelper::cancel('component.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_SITE_GLOBAL_CONFIGURATION');
	}
}
