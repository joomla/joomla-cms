<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.view');

/**
 * The HTML Members group view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @since		1.6
 */
class MembersViewGroup extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		parent::display($tpl);
		$this->_setToolbar();
		JRequest::setVar('hidemainmenu', 1);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolbar()
	{
		$isNew	= ($this->item->id == 0);
		JToolBarHelper::title(JText::_($isNew ? 'Members_Title_Add_Group' : 'Members_Title_Edit_Group'));

		JToolBarHelper::save('group.save');
		JToolBarHelper::apply('group.apply');
		JToolBarHelper::cancel('group.cancel');
		//JToolBarHelper::help('index', true);
	}
}