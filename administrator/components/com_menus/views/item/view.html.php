<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item View.
 *
 * @since  1.6
 */
class MenusViewItem extends JViewLegacy
{
	/**
	 * @var  JForm
	 */
	protected $form;

	/**
	 * @var  object
	 */
	protected $item;

	/**
	 * @var  mixed
	 */
	protected $modules;

	/**
	 * @var  JObject
	 */
	protected $state;

	/**
	 * @var  JObject
	 */
	protected $canDo;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->state   = $this->get('State');
		$this->form    = $this->get('Form');
		$this->item    = $this->get('Item');
		$this->modules = $this->get('Modules');
		$this->levels  = $this->get('ViewLevels');
		$this->canDo   = JHelperContent::getActions('com_menus', 'menu', (int) $this->state->get('item.menutypeid'));

		// Check if we're allowed to edit this item
		// No need to check for create, because then the moduletype select is empty
		if (!empty($this->item->id) && !$this->canDo->get('core.edit'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// If we are forcing a language in modal (used for associations).
		if ($this->getLayout() === 'modal' && $forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
		{
			// Set the language field to the forcedLanguage and disable changing it.
			$this->form->setValue('language', null, $forcedLanguage);
			$this->form->setFieldAttribute('language', 'readonly', 'true');

			// Only allow to select categories with All language or with the forced language.
			$this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);
		}

		parent::display($tpl);
		$this->addToolbar();
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
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', true);

		$user       = JFactory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo      = $this->canDo;

		JToolbarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_ITEM_TITLE' : 'COM_MENUS_VIEW_EDIT_ITEM_TITLE'), 'list menu-add');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create'))
		{
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('item.apply');
			}

			JToolbarHelper::save('item.save');
		}

		// If not checked out, can save the item.
		if (!$isNew && !$checkedOut && $canDo->get('core.edit'))
		{
			JToolbarHelper::apply('item.apply');
			JToolbarHelper::save('item.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('item.save2new');
		}

		// If an existing item, can save to a copy only if we have create rights.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('item.save2copy');
		}

		if ($isNew)
		{
			JToolbarHelper::cancel('item.cancel');
		}
		else
		{
			JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();

		// Get the help information for the menu item.
		$lang = JFactory::getLanguage();

		$help = $this->get('Help');

		if ($lang->hasKey($help->url))
		{
			$debug = $lang->setDebug(false);
			$url   = JText::_($help->url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = $help->url;
		}

		JToolbarHelper::help($help->key, $help->local, $url);
	}
}
