<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit an language override
 *
 * @package			Joomla.Administrator
 * @subpackage	com_languages
 * @since				2.5
 */
class LanguagesViewOverride extends JViewLegacy
{
	/**
	 * The form to use for the view
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $form;

	/**
	 * The item to edit
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $item;

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
	public function display($tpl = null)
	{
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root().'media/overrider/css/overrider.css');
		JHtml::_('behavior.framework');
		$doc->addScript(JURI::root().'media/overrider/js/overrider.js');

		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));

			return;
		}

		// Check whether the cache has to be refreshed
		$cached_time = JFactory::getApplication()->getUserState('com_languages.overrides.cachedtime.'.$this->state->get('filter.client').'.'.$this->state->get('filter.language'), 0);
		if(time() - $cached_time > 60 * 5)
		{
			$this->state->set('cache_expired', true);
		}

		// Add strings for translations in Javascript
		JText::script('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS');
		JText::script('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar
	 *
	 * @return void
	 *
	 * @since	2.5
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$canDo	= LanguagesHelper::getActions();

		JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_TITLE'), 'langmanager');

		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('override.apply');
			JToolBarHelper::save('override.save');
		}

		// This component does not support Save as Copy

		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolBarHelper::save2new('override.save2new');
		}

		if (empty($this->item->key))
		{
			JToolBarHelper::cancel('override.cancel');
		}
		else
		{
			JToolBarHelper::cancel('override.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_OVERRIDES_EDIT');
	}
}
