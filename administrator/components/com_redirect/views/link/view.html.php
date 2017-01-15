<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a redirect link.
 *
 * @since  1.6
 */
class RedirectViewLink extends JViewLegacy
{
	protected $item;

	protected $form;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		$canDo = JHelperContent::getActions('com_redirect');

		JToolbarHelper::title($isNew ? JText::_('COM_REDIRECT_MANAGER_LINK_NEW') : JText::_('COM_REDIRECT_MANAGER_LINK_EDIT'), 'refresh redirect');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('link.apply');
			JToolbarHelper::save('link.save');
		}

		/**
		 * This component does not support Save as Copy due to uniqueness checks.
		 * While it can be done, it causes too much confusion if the user does
		 * not change the Old URL.
		 */
		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolbarHelper::save2new('link.save2new');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('link.cancel');
		}
		else
		{
			JToolbarHelper::cancel('link.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_REDIRECT_MANAGER_EDIT');
	}
}
