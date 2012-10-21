<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');
JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

/**
 * Content Component Association Helper
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       3.0
 */
class ContentHelperAssociation
{
	public static function getAssociations($id = 0, $view = null)
	{
		jimport('helper.route', JPATH_COMPONENT_SITE);

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = is_null($view) ? $jinput->get('view') : $view;
		$id = empty($id) ? $jinput->getInt('id') : $id;

		if ($view == 'article')
		{
			if ($id)
			{
				$associations = ContentHelper::getAssociations($id);

				$return = array();

				foreach ($associations as $tag => $item) {

					$return[$tag] = ContentHelperRoute::getArticleRoute($item->id, $item->catid, $item->language);

				}

				return $return;
			}
		}

		if ($view == 'category' || $view == 'categories')
		{
			if ($id)
			{
				$associations = CategoriesHelper::getAssociations($id, 'com_content');

				$return = array();

				foreach ($associations as $tag => $item) {

					$return[$tag] = ContentHelperRoute::getCategoryRoute($item, $tag);

				}

				return $return;
			}
		}

		return array();

	}
}
