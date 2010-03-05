<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
class RedirectViewLink extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$canDo		= RedirectHelper::getActions();

		JToolBarHelper::title(JText::_('COM_REDIR_MANAGER_LINK'));

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('link.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('link.save', 'JTOOLBAR_SAVE');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('link.save2copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolBarHelper::addNew('link.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('link.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('link.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::help('screen.redirect.link','JTOOLBAR_HELP');
	}
}