<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Utitlity class for associations in multilang
 *
 * @since  3.1
 */
class JLanguageAssociations
{
	/**
	 * Get the associations.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tablename   The name of the table.
	 * @param   string   $context     The context.
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it.
	 * @param   string   $catField    If the table has a catid field set it here. Null to not use it.
	 * @param   boolean  $onlyIds     If the method should only return the ids.
	 *
	 * @return  array                The associated items.
	 *
	 * @since   3.1
	 *
	 * @throws  Exception
	 */
	public static function getAssociations($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid', $onlyIds = false)
	{
		// To avoid doing duplicate database queries.
		static $multilanguageAssociations = array();

		// Multilanguage association memory cache key (ignore onlyIds parameter).
		$argsToCache = func_get_args();
		unset($argsToCache[count($argsToCache) - 1]);
		$queryKey = md5(serialize($argsToCache));

		// If fetched before, don't fetch again.
		if (!isset($multilanguageAssociations[$queryKey]))
		{
			// Set it as an empty array by default.
			$multilanguageAssociations[$queryKey] = array();

			$db = JFactory::getDbo();
			$categoriesExtraSql = (($tablename === '#__categories') ? ' AND c2.extension = ' . $db->quote($extension) : '');
			$query = $db->getQuery(true)
				->select($db->quoteName('c2.language'))
				->from($db->quoteName($tablename, 'c'))
				->join('INNER', $db->quoteName('#__associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
				->join('INNER', $db->quoteName('#__associations', 'a2') . ' ON a.key = a2.key')
				->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk) . $categoriesExtraSql);

			// Use alias field ?
			if (!empty($aliasField))
			{
				$query->select(
					$query->concatenate(
						array(
							$db->quoteName('c2.' . $pk),
							$db->quoteName('c2.' . $aliasField)
						),
						':'
					) . ' AS ' . $db->quoteName($pk)
				);
			}
			else
			{
				$query->select($db->quoteName('c2.' . $pk));
			}

			// Use catid field ?
			if (!empty($catField))
			{
				$query->join(
						'INNER',
						$db->quoteName('#__categories', 'ca') . ' ON ' . $db->quoteName('c2.' . $catField) . ' = ca.id AND ca.extension = ' . $db->quote($extension)
					)
					->select(
						$query->concatenate(
							array('ca.id', 'ca.alias'),
							':'
						) . ' AS ' . $db->quoteName($catField)
					);
			}

			$query->where('c.' . $pk . ' = ' . (int) $id);
			if ($tablename === '#__categories')
			{
				$query->where('c.extension = ' . $db->quote($extension));
			}

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('language');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as $tag => $item)
				{
					// Do not return itself as result.
					if ((int) $item->{$pk} != $id)
					{
						$multilanguageAssociations[$queryKey][$tag] = $item;
					}
				}
			}
		}

		// Checks if we want to return the menu item id or the object.
		$associations = array();
		foreach ($multilanguageAssociations[$queryKey] as $tag => $item)
		{
			// Do not return itself as result.
			$associations[$tag] = ($onlyIds) ? $item->id : $item;
		}

		return $associations;
	}

	/**
	 * Get the associations links.
	 *
	 * @param   boolean  $lastHome  If we want the last resort to be the language homepage link.
	 *
	 * @return  array  The associated items links.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getAssociationsLinks($lastHome = false)
	{
		// To avoid doing duplicate processing.
		static $associationLinks = null;

		$app  = JFactory::getApplication();
		$menu = $app->getMenu();

		// If not yet fetched, fetch the associations array.
		if (is_null($associationLinks))
		{
			$associationLinks = array();

			// If multilanguage is enabled.
			if (JLanguageMultilang::isEnabled())
			{
				// Check what languages fullfill the requirements.
				$homepages        = JLanguageMultilang::getSiteHomePages();
				$languages        = array_intersect_key(JLanguageHelper::getLanguages('lang_code'), JLanguageMultilang::getSiteLangs(), $homepages);
				$levels           = JFactory::getUser()->getAuthorisedViewLevels();

				foreach ($languages as $i => $language)
				{
					// Do not display language without authorized access level in the language.
					if (isset($language->access) && $language->access && !in_array($language->access, $levels))
					{
						unset($languages[$i]);
						continue;
					}
					// Do not display language without authorized access level in the home menu item id.
					if (isset($homepages[$i]->level) && $homepages[$i]->level && !in_array($homepages[$i]->level, $levels))
					{
						unset($languages[$i]);
						continue;
					}
					// Set the home id for the language.
					$languages[$i]->homeid = $homepages[$i]->id;
				}

				// If any language left to process.
				if (count($languages) > 0)
				{
					$lang             = JFactory::getLanguage();
					$langTag          = $lang->getTag();
					$activeMenu       = $menu->getActive();
					$isHome           = (isset($activeMenu) && $languages[$langTag]->homeid == $activeMenu->id);
					$cassociations    = array();
					$associations     = array();

					// If is not the homepage, multilanguage associations are enable and associations is enabled load the associations.
					if (!$isHome && self::isEnabled())
					{
						// Load component associations.
						$cassociations = self::getComponentAssociations($app->input->get('option'));

						// Load menu associations (only if there is an active menu).
						if (isset($activeMenu))
						{
							$associations = self::getAssociations('com_menus', '#__menu', 'com_menus.item', $activeMenu->id, 'id', null, null, true);
						}
					}

					$internalUri = '';
					// For each language get the association link.
					foreach ($languages as $i => $language)
					{
						switch (true)
						{
							// If current URI is the home page the associations are the homepages in all languages.
							case ($isHome):
								$item = $menu->getItem($language->homeid);
								$associationLinks[$language->lang_code] = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
								break;

							// If the current language return current language link.
							case ($i === $langTag):
								$associationLinks[$i] = JUri::getInstance()->toString(array('path', 'query'));
								break;

							// If there is a menu item association for the current URI the association is that association in this language.
							case (isset($activeMenu) && isset($associations[$i]) && ($item = $menu->getItem($associations[$i]))):
								$associationLinks[$i] = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $language->sef);
								break;

							// If there is a component association (ex: category) for the current URI the association is that association in this language.
							case (isset($cassociations[$i])):
								$associationLinks[$i] = JRoute::_($cassociations[$i] . '&lang=' . $language->sef);
								break;

							// If current URI is a component without menu item (no active menu, ex: /en/component/content/),
							// associated URI for this language will be the version of the component in the language (ex: /fr/component/content/).
							case (!isset($activeMenu)):
								if (empty($internalUri))
								{
									$internalUri = http_build_query($app->getRouter()->getVars(array_diff_key($app->getRouter()->getVars(), array('lang' => ''))));
								}
								$associationLinks[$i] = JRoute::_('index.php?' . $internalUri . '&lang=' . $language->sef);
								break;

							// If no association ... set to this language home page menu item id or (if not exists) to the default global menu id to be treated after.
							default:
								$associationLinks[$i] = ($item = $menu->getItem($language->homeid)) ? $language->homeid : $homepages['*']->id;
								break;
						}
					}
				}
			}
		}

		// Change the last item in the array. Needed to not repeat the whole process two times
		// in each page load (language filter alternate meta and the language switcher module links).
		$associations = array_merge($associationLinks);
		foreach ($associationLinks as $i => $associationLink)
		{
			// If is the homepage item id setted above.
			if (is_numeric($associationLink))
			{
				// If we want as a last resort that the association link is the language homepage (used in language switcher module links).
				if ($lastHome)
				{
					$item = $menu->getItem($associationLink);
					$associations[$i] = JRoute::_($item->link . '&Itemid=' . $item->id . '&lang=' . $i);
				}
				// If no association ... unset the association link for this language (used in language filter alternate meta tags).
				else
				{
					unset($associations[$i]);
				}
			}
		}

		return $associations;
	}

	/**
	 * Get the component associations.
	 *
	 * @param   string  $component  The name of the component or extension id.
	 *
	 * @return  array  The associated items.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getComponentAssociations($component = 'com_content')
	{
		static $associations = array();

		// It was called by the extension id, not the component name. Gets the component name.
		if (strpos($component, '.') !== false)
		{
			$component = array_shift(explode('.', $component));
		}

		// If tested before, don't test again. Return the previous result.
		if (isset($associations[$component]))
		{
			return $associations[$component];
		}

		// Set it as an empty array by default.
		$associations[$component] = array();

		// If component allows associations return the associations.
		if (self::isEnabled())
		{
			$className = JString::ucfirst(JString::str_ireplace('com_', '', $component)) . 'HelperAssociation';
			if (!(class_exists($className) && is_callable(array($className, 'getAssociations'))))
			{
				JLoader::register($className, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));
			}
			if (class_exists($className) && is_callable(array($className, 'getAssociations')))
			{
				$associations[$component] = call_user_func(array($className, 'getAssociations'));
			}
		}

		return $associations[$component];
	}

	/**
	 * Check if a component allows language associations.
	 *
	 * @param   string  $component  The name of the component or extension id.
	 *
	 * @return  boolean  True if component allows associations; false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function allowsAssociations($component = 'com_content')
	{
		static $associations = array();

		// It was called by the extension id, not the component name. Gets the component name.
		if (strpos($component, '.') !== false)
		{
			$component = array_shift(explode('.', $component));
		}

		// If tested before, don't test again. Return the previous result.
		if (isset($associations[$component]))
		{
			return $associations[$component];
		}

		// Set it as false by default.
		$associations[$component] = false;

		// If language associations are enabled check if there is a associations class in the component folder.
		if (self::isEnabled())
		{
			$className = JString::ucfirst(JString::str_ireplace('com_', '', $component)) . 'HelperAssociation';
			if (!(class_exists($className) && is_callable(array($className, 'getAssociations'))))
			{
				JLoader::register($className, JPath::clean(JPATH_COMPONENT_SITE . '/helpers/association.php'));
			}
			$associations[$component] = class_exists($className) && is_callable(array($className, 'getAssociations'));
		}

		return $associations[$component];
	}

	/**
	 * Method to determine if the language filter Items Associations parameter is enabled.
	 * This works for both site and administrator.
	 *
	 * @return  boolean  True if the parameter is implemented; false otherwise.
	 *
	 * @since   3.2
	 */
	public static function isEnabled()
	{
		static $enabled = null;

		// If already tested, don't test again. Return the previous result.
		if (!is_null($enabled))
		{
			return $enabled;
		}

		// Set it as false by default.
		$enabled = false;

		// If multilanguage is enabled, languague filter plugin is enabled and item_association param is set, return true.
		if (JLanguageMultilang::isEnabled())
		{
			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');

			if (!empty($plugin))
			{
				$params = new Registry($plugin->params);
				$enabled  = (boolean) $params->get('item_associations', true);
			}
		}

		return $enabled;
	}
}
