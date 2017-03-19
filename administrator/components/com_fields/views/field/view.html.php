<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Field View
 *
 * @since  3.7.0
 */
class FieldsViewField extends JViewLegacy
{
	/**
	 * @var  JForm
	 *
	 * @since   3.7.0
	 */
	protected $form;

	/**
	 * @var  JObject
	 *
	 * @since   3.7.0
	 */
	protected $item;

	/**
	 * @var  JObject
	 *
	 * @since   3.7.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$this->canDo = JHelperContent::getActions($this->state->get('field.component'), 'field', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		JFactory::getApplication()->input->set('hidemainmenu', true);

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$component = $this->state->get('field.component');
		$section   = $this->state->get('field.section');
		$userId    = JFactory::getUser()->get('id');
		$canDo     = $this->canDo;

		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = JFactory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = JText::sprintf('COM_FIELDS_VIEW_FIELD_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE', JText::_(strtoupper($component)));

		// Prepare the toolbar.
		JToolbarHelper::title(
			$title,
			'puzzle field-' . ($isNew ? 'add' : 'edit') . ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-field-' .
			($isNew ? 'add' : 'edit')
		);

		// For new records, check the create permission.
		if ($isNew)
		{
			JToolbarHelper::saveGroup(
				[
					['apply', 'field.apply'],
					['save', 'field.save'],
					['save2new', 'field.save2new']
				],
				'btn-success'
			);

			JToolbarHelper::cancel('contact.cancel');
		}
		else
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable)
			{
				$toolbarButtons[] = ['apply', 'field.apply'];
				$toolbarButtons[] = ['save', 'field.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create'))
				{
					$toolbarButtons[] = ['save2new', 'field.save2new'];
				}
			}

			// If an existing item, can save to a copy.
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'field.save2copy'];
			}

			JToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			JToolbarHelper::cancel('contact.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_FIELDS_FIELDS_EDIT');
	}
}
