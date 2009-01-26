<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksViewWeblink extends JView
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
		JToolBarHelper::title(JText::_('Weblinks_Manager_Weblink'));
		JToolBarHelper::addNew('weblink.save2new', 'JToolbar_Save_and_new');
		JToolBarHelper::apply('weblink.apply');
		JToolBarHelper::save('weblink.save');
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('weblink.cancel');
		}
		else {
			JToolBarHelper::cancel('weblink.cancel', 'JToolbar_Close');
		}
		JToolBarHelper::help('screen.weblink.edit');
	}
}
