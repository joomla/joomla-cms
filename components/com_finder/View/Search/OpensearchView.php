<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Document\Opensearch\OpensearchUrl;
use Joomla\CMS\Uri\Uri;

/**
 * OpenSearch View class for Finder
 *
 * @since  2.5
 */
class OpensearchView extends AbstractView
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$doc = \JFactory::getDocument();
		$app = \JFactory::getApplication();

		$params = ComponentHelper::getParams('com_finder');
		$doc->setShortName($params->get('opensearch_name', $app->get('sitename')));
		$doc->setDescription($params->get('opensearch_description', $app->get('MetaDesc')));

		// Add the URL for the search
		$searchUri = Uri::base() . 'index.php?option=com_finder&q={searchTerms}';

		// Find the menu item for the search
		$menu  = $app->getMenu();
		$items = $menu->getItems('link', 'index.php?option=com_finder&view=search');

		if (isset($items[0]))
		{
			$searchUri .= '&Itemid=' . $items[0]->id;
		}

		$htmlSearch           = new OpensearchUrl;
		$htmlSearch->template = Route::_($searchUri);
		$doc->addUrl($htmlSearch);
	}
}
