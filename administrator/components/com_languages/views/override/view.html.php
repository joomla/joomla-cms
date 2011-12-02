<?php
/**
 * @version		$Id: view.html.php 21655 2011-06-23 05:43:24Z chdemko $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a redirect link.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class LanguagesViewOverride extends JView
{
	protected $item;
	protected $form;
	protected $state;

	/**
	 * Display the view
	 *
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
    $doc  = JFactory::getDocument();
    $doc->addStyleSheet(JURI::root().'media/system/css/overrider.css');
    JHTML::core();
    $doc->addScript(JURI::root().'media/system/js/overrider.js');

		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if(count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$cached_time = JFactory::getApplication()->getUserState('com_languages.overrides.cachedtime.'.$this->state->get('filter.client').'.'.$this->state->get('filter.language'), 0);
		if(time() - $cached_time > 60 * 5)
		{
			$this->state->set('cache_expired', true);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

    $app    = JFactory::getApplication();
		$user	  = JFactory::getUser();
		$canDo	= LanguagesHelper::getActions();

    $client   = $this->state->get('filter.client');
    $language = JLanguage::getInstance($this->state->get('filter.language'))->getName();
		JToolBarHelper::title(JText::sprintf('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_TITLE', JText::_('COM_LANGUAGES_VIEW_OVERRIDE_CLIENT_'.strtoupper($client)), $language), 'langmanager');

		if ($canDo->get('core.edit')) {
			JToolBarHelper::apply('override.apply');
			JToolBarHelper::save('override.save');
		}

		// This component does not support Save as Copy due to uniqueness checks.
		// While it can be done, it causes too much confusion if the user does
		// not change the Old URL.

		if ($canDo->get('core.edit') && $canDo->get('core.create')) {
			JToolBarHelper::save2new('override.save2new');
		}

		if (empty($this->item->key)) {
			JToolBarHelper::cancel('override.cancel');
		} else {
			JToolBarHelper::cancel('override.cancel', 'JTOOLBAR_CLOSE');
		}

		//JToolBarHelper::help('JHELP_COMPONENTS_OVERRIDER_EDIT');
	}
}
