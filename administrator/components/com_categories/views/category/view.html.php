<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Categories component
 *
 * @since  1.6
 */
class CategoriesViewCategory extends JViewLegacy
{
	/**
	 * The JForm object
	 *
	 * @var  JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Flag if an association exists
	 *
	 * @var  boolean
	 */
	protected $assoc;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  JObject
	 */
	protected $canDo;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$section = $this->state->get('category.section') ? $this->state->get('category.section') . '.' : '';
		$this->canDo = JHelperContent::getActions($this->state->get('category.component'), $section . 'category', $this->item->id);
		$this->assoc = $this->get('Assoc');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Check for tag type
		$this->checkTags = JHelperTags::getTypes('objectList', array($this->state->get('category.extension') . '.category'), true);

		JFactory::getApplication()->input->set('hidemainmenu', true);

		if ($this->getLayout() == 'modal')
		{
			$this->form->setFieldAttribute('language', 'readonly', 'true');
			$this->form->setFieldAttribute('parent_id', 'readonly', 'true');
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
		$extension = JFactory::getApplication()->input->get('extension');
		$user = JFactory::getUser();
		$userId = $user->id;

		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Check to see if the type exists
		$ucmType = new JUcmType;
		$this->typeId = $ucmType->getTypeId($extension . '.category');

		// Avoid nonsense situation.
		if ($extension == 'com_categories')
		{
			return;
		}

		// The extension can be in the form com_foo.section
		$parts = explode('.', $extension);
		$component = $parts[0];
		$section = (count($parts) > 1) ? $parts[1] : null;
		$componentParams = JComponentHelper::getParams($component);

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
		$lang->load($component, JPATH_BASE, null, false, true)
		|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component, null, false, true);

		// Load the category helper.
		JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

		// Get the results for each action.
		$canDo = $this->canDo;

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = $component . ($section ? "_$section" : '') . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE'))
		{
			$title = JText::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = $component . ($section ? "_$section" : '')))
		{
			$title = JText::sprintf('COM_CATEGORIES_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT')
					. '_TITLE', $this->escape(JText::_($component_section_key))
					);
		}
		// Else use the base title
		else
		{
			$title = JText::_('COM_CATEGORIES_CATEGORY_BASE_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE');
		}

		// Load specific css component
		JHtml::_('stylesheet', $component . '/administrator/categories.css', array(), true);

		// Prepare the toolbar.
		JToolbarHelper::title(
			$title,
			'folder category-' . ($isNew ? 'add' : 'edit')
				. ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-category-' . ($isNew ? 'add' : 'edit')
		);

		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories($component, 'core.create')) > 0))
		{
			JToolbarHelper::apply('category.apply');
			JToolbarHelper::save('category.save');
			JToolbarHelper::save2new('category.save2new');
			JToolbarHelper::cancel('category.cancel');
		}

		// If not checked out, can save the item.
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId);

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				JToolbarHelper::apply('category.apply');
				JToolbarHelper::save('category.save');

				if ($canDo->get('core.create'))
				{
					JToolbarHelper::save2new('category.save2new');
				}
			}

			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('category.save2copy');
			}

			if ($componentParams->get('save_history', 0) && $itemEditable)
			{
				$typeAlias = $extension . '.category';
				JToolbarHelper::versions($typeAlias, $this->item->id);
			}

			JToolbarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();

		// Compute the ref_key
		$ref_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_HELP_KEY';

		// Check if thr computed ref_key does exist in the component
		if (!$lang->hasKey($ref_key))
		{
			$ref_key = 'JHELP_COMPONENTS_'
						. strtoupper(substr($component, 4) . ($section ? "_$section" : ''))
						. '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT');
		}

		/*
		 * Get help for the category/section view for the component by
		 * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		 * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		 * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		 */
		if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL'))
		{
			$debug = $lang->setDebug(false);
			$url = JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else
		{
			$url = null;
		}

		JToolbarHelper::help($ref_key, $componentParams->exists('helpURL'), $url, $component);
	}
}
