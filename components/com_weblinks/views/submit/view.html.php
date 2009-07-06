<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewSubmit extends JView
{
	protected $state;
	protected $item;

	function display($tpl = null)
	{
		$app		= &JFactory::getApplication();
		$params		= &$app->getParams();

		// Get some data from the models
		$state		= &$this->get('State');
		$item		= &$this->get('Item');
		$category	= &$this->get('Category');

		if ($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		//get the weblink
		$weblink = &$this->get('data');

		if ($weblink->url) {
			// redirects to url if matching id found
			$app->redirect($weblink->url);
		}

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		// Get some objects from the JApplication
		$app	= &JFactory::getApplication();
		$pathway	= &$app->getPathway();
		$document	= &JFactory::getDocument();
		$model		= &$this->getModel();
		$user		= &JFactory::getUser();
		$uri     	= &JFactory::getURI();
		$params = &$app->getParams();

		// Make sure you are logged in and have the necessary access rights
		if ($user->authorise('com_weblinks.submit')) {
			  JResponse::setHeader('HTTP/1.0 403',true);
              JError::raiseWarning(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		//get the weblink
		$weblink	= &$this->get('data');
		$isNew	= ($weblink->id < 1);

		// Edit or Create?
		if (!$isNew)
		{
			// Is this link checked out?  If not by me fail
			//if ($model->isCheckedOut($user->get('id'))) {
			//	$app->redirect("index.php?option=$option", "The weblink $weblink->title is currently being edited by another administrator.");
			//}

			// Set page title
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();

			// because the application sets a default page title, we need to get it
			// right from the menu item itself
			if (is_object($menu)) {
				$menu_params = new JParameter($menu->params);
				if (!$menu_params->get('page_title')) {
					$params->set('page_title',	JText::_('Web Links'.' - '.JText::_('Edit')));
				}
			} else {
				$params->set('page_title',	JText::_('Web Links'.' - '.JText::_('Edit')));
			}

			$document->setTitle($params->get('page_title'));

			//set breadcrumbs
			if ($item->query['view'] != 'weblink')
			{
				switch ($item->query['view'])
				{
					case 'categories':
						$pathway->addItem($weblink->category, 'index.php?view=category&id='.$weblink->catid);
						$pathway->addItem(JText::_('Edit').' '.$weblink->title, '');
						break;
					case 'category':
						$pathway->addItem(JText::_('Edit').' '.$weblink->title, '');
						break;
				}
			}
		}
		else
		{
			/*
			 * The web link does not already exist so we are creating a new one.  Here
			 * we want to manipulate the pathway and pagetitle to indicate this.  Also,
			 * we need to initialize some values.
			 */
			$weblink->published = 0;
			$weblink->approved = 1;
			$weblink->ordering = 0;

			// Set page title
			// Set page title
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();

			// because the application sets a default page title, we need to get it
			// right from the menu item itself
			if (is_object($menu)) {
				$menu_params = new JParameter($menu->params);
				if (!$menu_params->get('page_title')) {
					$params->set('page_title', JText::_('Submit a Web Link'));
				}
			} else {
				$params->set('page_title', JText::_('Submit a Web Link'));
			}

			$document->setTitle($params->get('page_title'));

			// Add pathway item
			$pathway->addItem(JText::_('New'), '');
		}

		// build list of categories
		$lists['catid'] = JHtml::_('list.category', 'jform[catid]', 'com_weblinks', intval($weblink->catid));

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
			. ' FROM #__weblinks'
			. ' WHERE catid = ' . (int) $weblink->catid
			. ' ORDER BY ordering';

		$lists['ordering'] 			= JHtml::_('list.specificordering',  $weblink, $weblink->id, $query);

		// Radio Buttons: Should the article be published
		$lists['published'] 		= JHtml::_('select.booleanlist',  'jform[published]', 'class="inputbox"', $weblink->published);

		JFilterOutput::objectHTMLSafe($weblink, ENT_QUOTES, 'description');

		$this->assign('action', 	$uri->toString());

		$this->assignRef('lists'   , $lists);
		$this->assignRef('weblink' , $weblink);
		$this->assignRef('params' ,	 $params);
		parent::display($tpl);
	}
}
?>
