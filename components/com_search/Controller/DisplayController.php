<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Search\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Search Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool  $cachable   If true, the view output will be cached
	 * @param   bool  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		\JLoader::register('SearchHelper', JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php');

		// Force it to be the search view
		$this->input->set('view', 'search');

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Search
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function search()
	{
		// Slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');
		$searchword = trim(str_replace($badchars, '', $this->input->post->getString('searchword')));

		// If searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword, 0, 1) === '"' && substr($searchword, -1) === '"')
		{
			$post['searchword'] = substr($searchword, 1, -1);
			$this->input->set('searchphrase', 'exact');
		}
		else
		{
			$post['searchword'] = $searchword;
		}

		$post['ordering']     = $this->input->post->getWord('ordering');
		$post['searchphrase'] = $this->input->post->getWord('searchphrase', 'all');
		$post['limit']        = $this->input->post->getUInt('limit');

		if ($post['limit'] === null)
		{
			unset($post['limit']);
		}

		$areas = $this->input->post->get('areas', null, 'array');

		if ($areas)
		{
			foreach ($areas as $area)
			{
				$post['areas'][] = \JFilterInput::getInstance()->clean($area, 'cmd');
			}
		}

		// The Itemid from the request, we will use this if it's a search page or if there is no search page available
		$post['Itemid'] = $this->input->getInt('Itemid');

		// Set Itemid id for links from menu
		$menu = $this->app->getMenu();
		$item = $menu->getItem($post['Itemid']);

		// The requested Item is not a search page so we need to find one
		if ($item && ($item->component !== 'com_search' || $item->query['view'] !== 'search'))
		{
			// Get item based on component, not link. link is not reliable.
			$item = $menu->getItems('component', 'com_search', true);

			// If we found a search page, use that.
			if (!empty($item))
			{
				$post['Itemid'] = $item->id;
			}
		}

		unset($post['task'], $post['submit']);

		$uri = \JUri::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_search');

		$this->setRedirect(\JRoute::_('index.php' . $uri->toString(array('query', 'fragment')), false));
	}
}
