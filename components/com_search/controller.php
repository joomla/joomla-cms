<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search Component Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_search
 * @since       1.5
 */
class SearchController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->input->set('view', 'search'); // force it to be the search view

		return parent::display($cachable, $urlparams);
	}

	public function search()
	{
		// slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');
		$searchword = trim(str_replace($badchars, '', $this->input->getString('searchword', null, 'post')));
		// if searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"')
		{
			$post['searchword'] = substr($searchword, 1, -1);
			$this->input->set('searchphrase', 'exact');
		}
		else
		{
			$post['searchword'] = $searchword;
		}
		$post['ordering']     = $this->input->getWord('ordering', null, 'post');
		$post['searchphrase'] = $this->input->getWord('searchphrase', 'all', 'post');
		$post['limit']        = $this->input->getUInt('limit', null, 'post');

		if ($post['limit'] === null)
		{
			unset($post['limit']);
		}

		$areas = $this->input->post->get('areas', null, 'array');
		if ($areas)
		{
			foreach ($areas as $area)
			{
				$post['areas'][] = JFilterInput::getInstance()->clean($area, 'cmd');
			}
		}

		// The Itemid from the request, we will use this if it's a search page or if there is no search page available
		$post['Itemid'] = $this->input->getInt('Itemid');

		// Set Itemid id for links from menu
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$item = $menu->getItem($post['Itemid']);

		// The request Item is not a search page so we need to find one
		if ($item->component != 'com_search' || $item->query['view'] != 'search')
		{
			// Get item based on component, not link. link is not reliable.
			$item = $menu->getItems('component', 'com_search', true);

			// If we found a search page, use that.
			if (!empty($item))
			{
				$post['Itemid'] = $item->id;
			}
		}

		unset($post['task']);
		unset($post['submit']);

		$uri = JUri::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_search');

		$this->setRedirect(JRoute::_('index.php'.$uri->toString(array('query', 'fragment')), false));
	}
}
