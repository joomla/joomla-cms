<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Popular\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Model\ArticlesModel;
use Joomla\Registry\Registry;

/**
 * Helper for mod_popular
 *
 * @since  1.6
 */
abstract class PopularHelper
{
	/**
	 * Get a list of the most popular articles.
	 *
	 * @param   Registry       &$params  The module parameters.
	 * @param   ArticlesModel  $model    The model.
	 *
	 * @return  mixed  An array of articles, or false on error.
	 *
	 * @throws  \Exception
	 */
	public static function getList(Registry &$params, ArticlesModel $model)
	{
		$user = Factory::getUser();

		// Set List SELECT
		$model->setState('list.select', 'a.id, a.title, a.checked_out, a.checked_out_time, ' .
			' a.publish_up, a.hits'
		);

		// Set Ordering filter
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'DESC');

		// Set Category Filter
		$categoryId = $params->get('catid', null);

		if (is_numeric($categoryId))
		{
			$model->setState('filter.category_id', $categoryId);
		}

		// Set User Filter.
		$userId = $user->get('id');

		switch ($params->get('user_id', '0'))
		{
			case 'by_me':
				$model->setState('filter.author_id', $userId);
				break;

			case 'not_me':
				$model->setState('filter.author_id', $userId);
				$model->setState('filter.author_id.include', false);
				break;
		}

		// Set the Start and Limit
		$model->setState('list.start', 0);
		$model->setState('list.limit', $params->get('count', 5));

		$items = $model->getItems();

		if ($error = $model->getError())
		{
			throw new \Exception($error, 500);
		}

		// Set the links
		foreach ($items as &$item)
		{
			$item->link = '';

			if ($user->authorise('core.edit', 'com_content.article.' . $item->id))
			{
				$item->link = Route::_('index.php?option=com_content&task=article.edit&id=' . $item->id);
			}
		}

		return $items;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param   Registry  $params  The module parameters.
	 *
	 * @return  string	The alternate title for the module.
	 */
	public static function getTitle($params)
	{
		$who   = $params->get('user_id', 0);
		$catid = (int) $params->get('catid', null);
		$title = '';

		if ($catid)
		{
			$category = Categories::getInstance('Content')->get($catid);
			$title    = Text::_('MOD_POPULAR_UNEXISTING');

			if ($category)
			{
				$title = $category->title;
			}
		}

		return Text::plural(
			'MOD_POPULAR_TITLE' . ($catid ? '_CATEGORY' : '') . ($who != '0' ? "_$who" : ''),
			(int) $params->get('count', 5),
			$title
		);
	}
}
