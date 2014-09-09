<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Modules component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ModulesViewSelect extends JViewLegacy
{
	protected $state;

	protected $items;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state = &$state;
		$this->items = &$items;

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		// Add page title
		JToolbarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES'), 'cube module');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('toolbar.cancelselect');

		$bar->appendButton('Custom', $layout->render(array()), 'new');
	}
}
