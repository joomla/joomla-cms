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
 * View class for a list of comments.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		1.6
 */
class CommentsViewComments extends JView
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

		parent::display($tpl);
		$this->_setToolbar();
	}

	function getContentRoute($url)
	{
		static $router;

		// Only get the router once.
		if (!is_object($router))
		{
			// Import dependencies.
			jimport('joomla.application.router');
			require_once(JPATH_SITE.DS.'includes'.DS.'application.php');

			// Get the site router.
			$config	= &JFactory::getConfig();
			$router = JRouter::getInstance('site');
			$router->setMode($config->getValue('sef', 1));
		}

		// Build the route.
		$uri	= &$router->build($url);
		$route	= $uri->toString(array('path', 'query', 'fragment'));

		// Strip out the base portion of the route.
		$route = str_replace('administrator/', '', $route);

		return $route;
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		$state	= $this->get('State');
		$canDo	= CommentsHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title('Comments: '.JText::_('COMMENTS_MODERATE_COMMENTS_TITLE'), 'logo');

		$toolbar = JToolBar::getInstance('toolbar');
		$toolbar->appendButton('Standard', 'save', 'COMMENTS_MODERATE', 'comment.moderate', false, false);

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_comments');
		}
		JToolBarHelper::help('screen.comments','JTOOLBAR_HELP');
	}
}