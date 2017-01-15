<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a newsfeed.
 *
 * @since  1.6
 */
class NewsfeedsViewNewsfeed extends JViewLegacy
{
	/**
	 * The item object for the newsfeed
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The form object for the newsfeed
	 *
	 * @var    JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The model state of the newsfeed
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

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
			$this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

			// Only allow to select tags with All language or with the forced language.
			$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user       = JFactory::getUser();
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		// Since we don't track these assets at the item level, use the category id.
		$canDo = JHelperContent::getActions('com_newsfeeds', 'category', $this->item->catid);

		JToolbarHelper::title($isNew ? JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_NEW') : JText::_('COM_NEWSFEEDS_MANAGER_NEWSFEED_EDIT'), 'feed newsfeeds');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0))
		{
			JToolbarHelper::apply('newsfeed.apply');
			JToolbarHelper::save('newsfeed.save');
		}
		if (!$checkedOut && count($user->getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			JToolbarHelper::save2new('newsfeed.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('newsfeed.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('newsfeed.cancel');
		}
		else
		{
			if (JComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				JToolbarHelper::versions('com_newsfeeds.newsfeed', $this->item->id);
			}

			JToolbarHelper::cancel('newsfeed.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS_EDIT');
	}
}
