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

		JToolBarHelper::title(JText::_('Redir_Manager_Link'));

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('link.apply', 'JToolbar_Apply');
			JToolBarHelper::save('link.save', 'JToolbar_Save');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('link.save2copy�', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolBarHelper::addNew('link.save2new', 'JToolbar_Save_and_new');
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('link.cancel', 'JToolbar_Cancel');
		}
		else {
			JToolBarHelper::cancel('link.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::help('screen.redirect.link','JTOOLBAR_HELP');
	}
}