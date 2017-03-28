<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a template style.
 *
 * @since  1.6
 */
class TemplatesViewStyle extends JViewLegacy
{
	/**
	 * The JObject (on success, false on failure)
	 *
	 * @var   JObject
	 */
	protected $item;

	/**
	 * The form object
	 *
	 * @var   JForm
	 */
	protected $form;

	/**
	 * The model state
	 *
	 * @var   JObject
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->canDo = JHelperContent::getActions('com_templates');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();

		return parent::display($tpl);
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

		$isNew = ($this->item->id == 0);
		$canDo = $this->canDo;

		JToolbarHelper::title(
			$isNew ? JText::_('COM_TEMPLATES_MANAGER_ADD_STYLE')
			: JText::_('COM_TEMPLATES_MANAGER_EDIT_STYLE'), 'eye thememanager'
		);

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('style.apply');
			JToolbarHelper::save('style.save');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('style.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('style.cancel');
		}
		else
		{
			JToolbarHelper::cancel('style.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();

		// Get the help information for the template item.
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
