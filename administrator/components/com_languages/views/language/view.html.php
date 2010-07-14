<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Languages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.5
 */
class LanguagesViewLanguage extends JView
{
	public $item;
	public $form;
	public $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);
		$isNew = empty($this->item->lang_id);

		JToolBarHelper::title(JText::_($isNew ? 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_NEW_TITLE' : 'COM_LANGUAGES_VIEW_LANGUAGE_EDIT_EDIT_TITLE'), 'langmanager.png');
		JToolBarHelper::save('language.save','JTOOLBAR_SAVE');
		JToolBarHelper::apply('language.apply','JTOOLBAR_APPLY');
		JToolBarHelper::custom('language.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		if ($isNew)  {
			JToolBarHelper::cancel('language.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('language.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_EDIT');
	}
}
