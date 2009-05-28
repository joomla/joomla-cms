<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Search Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	Search
 * @since 1.5
 */
class SearchController extends JController
{
	/**
	 * Method to show the search view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		JRequest::setVar('view','search'); // force it to be the polls view
		parent::display();
	}

	function search()
	{
		// slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#','>','<','\\');
		$searchword = trim(str_replace($badchars, '', JRequest::getString('searchword', null, 'post')));
		// if searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword,0,1) == '"' && substr($searchword, -1) == '"') {
			$post['searchword'] = substr($searchword,1,-1);
			JRequest::setVar('searchphrase', 'exact');
		}
		else {
			$post['searchword'] = $searchword;
		}
		$post['ordering']	= JRequest::getWord('ordering', null, 'post');
		$post['searchphrase']	= JRequest::getWord('searchphrase', 'all', 'post');
		$post['limit']  = JRequest::getInt('limit', null, 'post');
		if ($post['limit'] === null) unset($post['limit']);

		$areas = JRequest::getVar('areas', null, 'post', 'array');
		if ($areas) {
			foreach($areas as $area)
			{
				$post['areas'][] = JFilterInput::clean($area, 'cmd');
			}
		}

		// No need to guess Itemid if it's already present in the URL
		if (JRequest::getInt('Itemid') > 0) {
			$post['Itemid'] = JRequest::getInt('Itemid');
		} else {

			// set Itemid id for links
			$menu = &JSite::getMenu();
			$items	= $menu->getItems('link', 'index.php?option=com_search&view=search');

			if (isset($items[0])) {
				$post['Itemid'] = $items[0]->id;
			}

		}

		unset($post['task']);
		unset($post['submit']);

		$uri = JURI::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_search');


		$this->setRedirect(JRoute::_('index.php'.$uri->toString(array('query', 'fragment')), false));
	}
}
