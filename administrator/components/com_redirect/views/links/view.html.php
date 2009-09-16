<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.view');

/**
 * The HTML Redirect links view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectViewLinks extends JView
{
	/**
	 * Display the view.
	 *
	 * @since	1.6
	 */
	function display($tpl = null)
	{
		// Get data from the model.
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Build the state filter options.
		$poptions[] = JHtml::_('select.option','*', 'Any');
		$poptions[] = JHtml::_('select.option', '0', 'Pending');
		$poptions[] = JHtml::_('select.option', '1', 'Active');
		$poptions[] = JHtml::_('select.option', '2', 'Archived');

		// Assign data to the view.
		$this->assignRef('state', $state);
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('filter_state', $poptions);

		// Render the layout.
		parent::display($tpl);
	}

	/**
	 * Build the default toolbar.
	 *
	 * @access	protected
	 * @return	void
	 * @since	1.6
	 */
	function buildDefaultToolBar()
	{
		JToolBarHelper::title('Redirect', 'redirect');
		JToolBarHelper::custom('link.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('link.edit', 'edit.png', 'edit_f2.png', 'Edit', true);
		JToolBarHelper::divider()	;	
		JToolBarHelper::custom('link.activate', 'default.png', 'default_f2.png', 'Activate', true);
		JToolBarHelper::custom('link.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
		JToolBarHelper::custom('link.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
		JToolBarHelper::custom('link.archive', 'archive.png', 'archive_f2.png', 'Archive', true);
		JToolBarHelper::deleteList('Are you sure you want to remove these links?', 'link.delete', 'delete');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.redirect');		
	}
}