<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View for language overrides list
 *
 * @package			Joomla.Administrator
 * @subpackage	com_languages
 * @since				2.5
 */
class LanguagesViewOverrides extends JView
{
	/**
	 * The items to list
	 *
	 * @var		array
	 * @since	2.5
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $state;

	/**
	 * Displays the view
	 *
	 * @param		string	$tpl	The name of the template file to parse
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	function display($tpl = null)
	{
		jimport('joomla.language.helper');

		// Get data from the model
		$this->state			= $this->get('State');
		$this->items			= $this->get('Overrides');
		$this->languages	= $this->get('Languages');
		$this->pagination	= $this->get('Pagination');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));

			return;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	protected function addToolbar()
	{
		// Get the results for each action
		$canDo = LanguagesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_OVERRIDES_TITLE'), 'langmanager');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('override.add');
		}

		if ($canDo->get('core.edit') && $this->pagination->total)
		{
			JToolbarHelper::editList('override.edit');
		}

		if ($canDo->get('core.delete') && $this->pagination->total)
		{
			JToolbarHelper::deleteList('', 'overrides.delete');
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_languages');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_OVERRIDES');
	}
}
