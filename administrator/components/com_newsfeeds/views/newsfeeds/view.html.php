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
 * HTML View class for the Newsfeeds component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.5
 */
class NewsfeedsViewNewsfeeds extends JView
{
	public $state;
	public $items;
	public $pagination;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->assignRef('state',			$state);
		$this->assignRef('items',			$items);
		$this->assignRef('pagination',		$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		JHtml::_('behavior.modal', 'a.modal');
		JToolBarHelper::title(JText::_('Newsfeeds_Manager_Newsfeeds'), 'generic.png');
		JToolBarHelper::addNew('newsfeed.add');		
		JToolBarHelper::editList('newsfeed.edit');	
		JToolBarHelper::divider();
		JToolBarHelper::publishList('newsfeeds.publish');
		JToolBarHelper::unpublishList('newsfeeds.unpublish');
		JToolBarHelper::deleteList('', 'newsfeeds.delete');

		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_newsfeeds','400');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.newsfeed');
	}
}
