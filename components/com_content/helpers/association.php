<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/content.php');
JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Content Component Association Helper
 *
 * @since  3.0
 */
abstract class ContentHelperAssociation extends CategoryHelperAssociation
{
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
		$jinput = JFactory::getApplication()->input;
		$view   = is_null($view) ? $jinput->get('view') : $view;
		$id     = empty($id) ? $jinput->getInt('id') : $id;

		if ($view == 'article' || $view == 'category' || $view == 'featured')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $id);

				$return = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = ContentHelperRoute::getArticleRoute($item->id, (int) $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view == 'category' || $view == 'categories')
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
	 * @return  string   The url of each associated article
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function displayAssociations($id)
	{
		$url_assoc    = '';
		$associations = self::getAssociations($id);

		if (!empty($associations))
		{
			jimport('joomla.application.component.helper');
			$params    = JComponentHelper::getParams('com_content');
			$levels    = JFactory::getUser()->getAuthorisedViewLevels();
			$languages = JLanguageHelper::getLanguages();

			foreach ($associations as $key => $value)
			{
				foreach ($languages as $language)
				{
					// Do not display language without frontend UI
					if (!array_key_exists($language->lang_code, JLanguageMultilang::getSiteLangs()))
					{
						$key == null;
					}
					// Do not display language without specific home menu
					elseif (!array_key_exists($key, JLanguageMultilang::getSiteHomePages()))
					{
						$key == null;
					}
					// Do not display language without authorized access level
					elseif (isset($language->access) && $language->access && !in_array($language->access, $levels))
					{
						$key == null;
					}
					elseif (isset($key) && ($key == $language->lang_code))
					{
						$class = 'label label-association label-' . $language->sef;
						$url   = '&nbsp;<a class="' . $class . '" href="' . JRoute::_($value) . '">' . strtoupper($language->sef) . '</a>&nbsp;';

						if ($params->get('flags', 1))
						{
							$flag = JHtml::_('image', 'mod_languages/' . $language->image . '.gif',
									$language->title_native, array('title' => $language->title_native), true
									);
							$url  = '&nbsp;<a href="' . JRoute::_($value) . '">' . $flag . '</a>&nbsp;';
						}

						$url_assoc .= $url;
					}
				}
			}
		}

		return $url_assoc;
	}
}
