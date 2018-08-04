<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Search\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Document\Opensearch\OpensearchUrl;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * OpenSearch View class for the Search component
 *
 * @since  1.7
 */
class OpensearchView extends AbstractView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  name of the template
	 *
	 * @throws \Exception
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$doc = Factory::getDocument();
		$app = Factory::getApplication();

		$params = ComponentHelper::getParams('com_search');
		$doc->setShortName($params->get('opensearch_name', $app->get('sitename')));
		$doc->setDescription($params->get('opensearch_description', $app->get('MetaDesc')));

		// Add the URL for the search
		$searchUri = Uri::base() . 'index.php?option=com_search&searchword={searchTerms}';

		// Find the menu item for the search
		$items = $app->getMenu()->getItems('link', 'index.php?option=com_search&view=search');

		if (isset($items[0]))
		{
			$searchUri .= '&Itemid=' . $items[0]->id;
		}

		$htmlSearch           = new OpensearchUrl;
		$htmlSearch->template = Route::_($searchUri);
		$doc->addUrl($htmlSearch);
	}
}
