<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a plugin.
 *
 * @since  1.5
 */
class PluginsViewPlugin extends JViewLegacy
{
	protected $item;

	protected $form;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$canDo = JHelperContent::getActions('com_plugins');

		JToolbarHelper::title(JText::sprintf('COM_PLUGINS_MANAGER_PLUGIN', JText::_($this->item->name)), 'power-cord plugin');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('plugin.apply');
			JToolbarHelper::save('plugin.save');
		}

		JToolbarHelper::cancel('plugin.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::divider();

		// Get the help information for the plugin item.
		$lang = JFactory::getLanguage();

		$help = $this->get('Help');

		if ($lang->hasKey($help->url))
		{
			$debug = $lang->setDebug(false);
			$url = JText::_($help->url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		JToolbarHelper::help($help->key, false, $url);
	}
}
