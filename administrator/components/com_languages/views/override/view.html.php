<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a language override
 *
 * @since  2.5
 */
class LanguagesViewOverride extends JViewLegacy
{
	/**
	 * The form to use for the view.
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $form;

	/**
	 * The item to edit.
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $item;

	/**
	 * The model state.
	 *
	 * @var		object
	 * @since	2.5
	 */
	protected $state;

	/**
	 * Displays the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		$app = JFactory::getApplication();

		$languageClient = $app->getUserStateFromRequest('com_languages.overrides.language_client', 'language_client');

		if ($languageClient == null)
		{
			$app->enqueueMessage(JText::_('COM_LANGUAGES_OVERRIDE_FIRST_SELECT_MESSAGE'), 'warning');

			$app->redirect('index.php?option=com_languages&view=overrides');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Check whether the cache has to be refreshed.
		$cached_time = JFactory::getApplication()->getUserState(
			'com_languages.overrides.cachedtime.' . $this->state->get('filter.client') . '.' . $this->state->get('filter.language'),
			0
		);

		if (time() - $cached_time > 60 * 5)
		{
			$this->state->set('cache_expired', true);
		}

		// Add strings for translations in Javascript.
		JText::script('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS');
		JText::script('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since	2.5
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$canDo = JHelperContent::getActions('com_languages');

		JToolbarHelper::title(JText::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_TITLE'), 'comments-2 langmanager');

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('override.apply');
			JToolbarHelper::save('override.save');
		}

		// This component does not support Save as Copy.
		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolbarHelper::save2new('override.save2new');
		}

		if (empty($this->item->key))
		{
			JToolbarHelper::cancel('override.cancel');
		}
		else
		{
			JToolbarHelper::cancel('override.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_OVERRIDES_EDIT');
	}
}
