<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentViewKeywords extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
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

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 *
	 * @access	private
	 */
	protected function _setToolbar()
	{
		$state = $this->get('State');
		JToolBarHelper::title(JText::_('Content_Keywords_Title'), 'article.png');
		JToolBarHelper::custom('keywords.repair', 'refresh.png', 'refresh_f2.png', 'Audit & Repair', false);
		JToolBarHelper::custom('keywords.rebuild', 'refresh.png', 'refresh_f2.png', 'Rebuild', false);
		JToolBarHelper::help('screen.content.articles');
	}
}