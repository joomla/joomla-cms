<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.5
 */
class NewsfeedsViewNewsfeed extends JView
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

		JToolBarHelper::title(JText::_('Newsfeeds_Manager_Newsfeed'));
		JToolBarHelper::save('newsfeed.save');	
		JToolBarHelper::apply('newsfeed.apply');	
		JToolBarHelper::addNew('newsfeed.save2new', 'JToolbar_Save_and_new');
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('newsfeed.cancel');
		}
		else {
			JToolBarHelper::cancel('newsfeed.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::help('screen.newsfeed.edit');
	}
}
