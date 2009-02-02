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
 * The HTML Members access levels view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @version		1.6
 */
class MembersViewLevels extends JView
{
	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @access	protected
	 * @return	void
	 * @since	1.0
	 */
	function buildDefaultToolBar()
	{
		JToolBarHelper::title(JText::_('Members_Title_Access_Levels'));

		JToolBarHelper::custom('level.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('level.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::deleteList('', 'level.delete');

		JToolBarHelper::divider();

		// We can't use the toolbar helper here because there is no generic popup button.
		$bar = &JToolBar::getInstance('toolbar');
		$bar->appendButton('Popup', 'config', 'JToolbar_Options', 'index.php?option=com_members&view=config&tmpl=component', 570, 500);

		//JToolBarHelper::help('index', true);
	}
}
