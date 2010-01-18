<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Hybrid view to display or edit a newsfeed.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class CommentsViewComment extends JView
{
	protected $state;
	protected $item;
	protected $form;
	protected $threadList;
	protected $nameList;
	protected $addressList;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$editMode		= ($this->getLayout() == 'edit');
		$state			= $this->get('State');
		$item			= $this->get('Item');
		$thread			= $this->get('Thread');

		if ($editMode) {
			$form = $this->get('Form');
		} else {
			$threadList		= $this->get('ListByThread');
			$nameList		= $this->get('ListByName');
			$addressList	= $this->get('ListByIP');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($editMode) {
			// Bind the record to the form.
			$form->bind($item);
			$this->assignRef('form', $form);
		} else {
			$this->assignRef('threadList',	$threadList);
			$this->assignRef('nameList',	$nameList);
			$this->assignRef('addressList',	$addressList);
		}

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('thread',		$thread);

		jimport('joomla.html.bbcode');
		$parser = &JBBCode::getInstance(array(
			'smiley_path' => JPATH_ROOT.'/media/jxtended/img/smilies/default',
			'smiley_url' => JURI::root().'/media/jxtended/img/smilies/default'
		));
		$this->assignRef('bbcode', $parser);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		if ($this->getLayout() == 'edit') {
			JRequest::setVar('hidemainmenu', true);
			JToolBarHelper::title('Comments: '.JText::_('COMMENTS_EDIT_COMMENT'), 'logo');
			JToolBarHelper::save('comment.save');
			JToolBarHelper::cancel('comment.cancel');
		} else {
			JToolBarHelper::title('Comments: '.JText::_('COMMENTS_MODERATE_COMMENT'), 'logo');
			JToolBarHelper::custom('comment.edit', 'edit.png', 'edit_f2.png', 'JToolbar_Edit', false);
			JToolBarHelper::cancel('comment.cancel');
		}
	}

	function getContentRoute($url)
	{
		static $router;

		// Only get the router once.
		if (!is_object($router)) {
			// Import dependencies.
			jimport('joomla.application.router');
			require_once JPATH_SITE.'/includes/application.php';

			// Get the site router.
			$config	= JFactory::getConfig();
			$router = JRouter::getInstance('site');
			$router->setMode($config->getValue('sef', 1));
		}

		// Build the route.
		$uri	= $router->build($url);
		$route	= $uri->toString(array('path', 'query', 'fragment'));

		// Strip out the base portion of the route.
		$route = str_replace('administrator/', '', $route);

		return $route;
	}
}