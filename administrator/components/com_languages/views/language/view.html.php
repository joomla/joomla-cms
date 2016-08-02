<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Languages component.
 *
 * @since  1.5
 */
class LanguagesViewLanguage extends JViewLegacy
{
	public $item;

	public $form;

	public $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_languages');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
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
		JLoader::register('LanguagesHelper', JPATH_ADMINISTRATOR . '/components/com_languages/helpers/languages.php');

		JFactory::getApplication()->input->set('hidemainmenu', 1);
		$isNew = empty($this->item->lang_id);
		$canDo = $this->canDo;

		JToolbarHelper::title(
			JText::_($isNew ? 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_NEW_TITLE' : 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_EDIT_TITLE'), 'comments-2 langmanager'
		);

		if (($isNew && $canDo->get('core.create')) || (!$isNew && $canDo->get('core.edit')))
		{
			JToolbarHelper::apply('language.apply');
			JToolbarHelper::save('language.save');
		}

		// If an existing item, can save to a copy only if we have create rights.
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('language.save2new');
		}

		if ($isNew)
		{
			JToolbarHelper::cancel('language.cancel');
		}
		else
		{
			JToolbarHelper::cancel('language.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_EDIT');
	}
}
