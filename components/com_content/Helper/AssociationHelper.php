<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * Content Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
	/**
	 * Cached array of the content item id.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $filters = array();

	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id    Id of the item
	 * @param   string   $view  Name of the view
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		$jinput = Factory::getApplication()->input;
		$view   = $view ?? $jinput->get('view');
		$id     = empty($id) ? $jinput->getInt('id') : $id;
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if ($view === 'article')
		{
			if ($id)
			{
				if (!isset(static::$filters[$id]))
				{
					$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $id);

					$return = array();

					foreach ($associations as $tag => $item)
					{
						if ($item->language != Factory::getLanguage()->getTag())
						{
							$arrId = explode(':', $item->id);
							$assocId = $arrId[0];

							$db = Factory::getDbo();
							$query = $db->getQuery(true)
								->select($db->quoteName('state'))
								->from($db->quoteName('#__content'))
								->where($db->quoteName('id') . ' = ' . (int) $assocId)
								->whereIn($db->qn('access'), $groups);
							$db->setQuery($query);

							$result = (int) $db->loadResult();

							if ($result > 0)
							{
								$return[$tag] = \ContentHelperRoute::getArticleRoute((int) $item->id, (int) $item->catid, $item->language);
							}
						}

						static::$filters[$id] = $return;
					}


					if (count($associations) === 0)
					{
						static::$filters[$id] = array();
					}
				}

				return static::$filters[$id];
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return self::getCategoryAssociations($id, 'com_content');
		}

		return array();
	}

	/**
	 * Method to display in frontend the associations for a given article
	 *
	 * @param   integer  $id  Id of the article
	 *
	 * @return  array   An array containing the association URL and the related language object
	 *
	 * @since  3.7.0
	 */
	public static function displayAssociations($id)
	{
		$return = array();

		if ($associations = self::getAssociations($id, 'article'))
		{
			$levels    = Factory::getUser()->getAuthorisedViewLevels();
			$languages = LanguageHelper::getLanguages();

			foreach ($languages as $language)
			{
				// Do not display language when no association
				if (empty($associations[$language->lang_code]))
				{
					continue;
				}

				// Do not display language without frontend UI
				if (!array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0)))
				{
					continue;
				}

				// Do not display language without specific home menu
				if (!array_key_exists($language->lang_code, Multilanguage::getSiteHomePages()))
				{
					continue;
				}

				// Do not display language without authorized access level
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					continue;
				}

				$return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
			}
		}

		return $return;
	}
}
