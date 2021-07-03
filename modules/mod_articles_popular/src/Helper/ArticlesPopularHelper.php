<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesPopular\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * Helper for mod_articles_popular
 *
 * @since  1.6
 */
abstract class ArticlesPopularHelper
{
	/**
	 * Get a list of popular articles from the articles model
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 */
	public static function getList(&$params)
	{
		$app = Factory::getApplication();

		// Get an instance of the generic articles model
		$model = $app->bootComponent('com_content')
			->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);

		// Set application parameters in model
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		$model->setState('list.start', 0);
		$model->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);

		// Set the filters based on the module params
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.featured', $params->get('show_front', 1) == 1 ? 'show' : 'hide');

		// This module does not use tags data
		$model->setState('load_tags', false);

		// Access filter
		$access = !ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', []));

		// Date filter
		$date_filtering = $params->get('date_filtering', 'off');

		if ($date_filtering !== 'off')
		{
			$model->setState('filter.date_filtering', $date_filtering);
			$model->setState('filter.date_field', $params->get('date_field', 'a.created'));
			$model->setState('filter.start_date_range', $params->get('start_date_range', '1000-01-01 00:00:00'));
			$model->setState('filter.end_date_range', $params->get('end_date_range', '9999-12-31 23:59:59'));
			$model->setState('filter.relative_date', $params->get('relative_date', 30));
		}

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Ordering
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');

		$items = $model->getItems();

		foreach ($items as &$item)
		{
			$item->slug = $item->id . ':' . $item->alias;

			if ($access || \in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$item->link = Route::_('index.php?option=com_users&view=login');
			}
		}

		return $items;
	}
}
