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
	public $state;
	public $item;
	public $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');
		$form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the label to the form.
		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', 1);
		$isNew = empty($this->item->lang_id);

		JToolBarHelper::title(JText::_($isNew ? 'Langs_View_Language_Edit_New_Title' : 'Langs_View_Language_Edit_Edit_Title'));
		JToolBarHelper::save('language.save','JTOOLBAR_SAVE');
		JToolBarHelper::apply('language.apply','JTOOLBAR_APPLY');
		JToolBarHelper::addNew('language.save2new', 'JToolbar_Save_and_new');
		if ($isNew)  {
			JToolBarHelper::cancel('language.cancel','JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('language.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.language.edit','JTOOLBAR_HELP');
	}
}
