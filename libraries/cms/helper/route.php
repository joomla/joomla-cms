<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  CMS
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Route Helper
 *
 * A class providing basic routing for urls that are for content types found in
 * the #__content_types table and rows found in the #__core_content table.
 *
 * @package     Joomla.Libraries
 * @subpackage  CMS
 * @since       3.1
 */
class JHelperRoute
{
	/**
	 * @var    array  Holds the reverse lookup
	 * @since  3.1
	 */
	protected static $lookup;

	/**
	 * @var    string  Option for the extension (such as com_content)
	 * @since  3.1
	 */
	protected  $extension;

	/**
	 * @var    string  Value of the primary key in the content type table
	 * @since  3.1
	 */
	protected  $id;

	/**
	 * @var    string  Name of the view for the url
	 * @since  3.1
	 */
	protected  $view;

	/**
	 * A method to get the route for a specific item
	 *
	 * @param   integer  $id         Value of the primary key for the item in its content table
	 * @param   string   $typealias  The type_alias for the item being routed. Of the form extension.view.
	 * @param   string   $link       The link to be routed
	 * @param   string   $language   The language of the content for multilingual sites
	 * @param   integer  $catid      Optional category id
	 *
	 * @return  string  The route of the item
	 *
	 * @since   3.1
	 */
	public static function getRoute($id, $typeAlias, $link = '', $language = null, $catid = null)
	{
		$typeExploded = explode('.', $typeAlias);

		$view = $typeExploded[1];
		$extension = $typeExploded[0];
		$name = ucfirst(substr_replace($extension, '', 0, 4));

		if (isset($view))
		{
			$needles = array(
				$view  => array((int) $id)
			);
		}
		if (!isset($link))
		{
			// Create the link
			$link = 'index.php?option=' . $this->extension . '&view=' . $this->view . '&id=' . $id;
		}

		if ($catid > 1)
		{var_dump($name);die;
			$categories = JCategories::getInstance($name);var_dump($categories);
			$category = $categories->get((int) $catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid=' . $catid;
			}
		}

		// Deal with languages only if needed
		if (!empty($language) && $language != '*' && JLanguageMultilang::isEnabled())
		{
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select('a.sef AS sef');
			$query->select('a.lang_code AS lang_code');
			$query->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();
			foreach ($langs as $lang)
			{
				if ($language == $lang->lang_code)
				{
					$link .= '&lang=' . $lang->sef;
					$needles['language'] = $language;
				}
			}
		}

			if ($item = self::findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
			elseif ($item = self::findItem())
			{
				$link .= '&Itemid=' . $item;
			}

		return $link;
	}

	/**
	 * Tries to load the router for the component and calls it. Otherwise uses getRoute.
	 * Assumes conformity to standard Joomla locations and naming structures.
	 *
	 * @param  integer   $contentItemId      component item id
	 * @param  string    $contentItemAlias  component item alias
	 * @param  integer   $contentCatId      component item category id
	 * @param  string    $language          component item language
	 * @param  string    $typeAlias         component type alias of the form com_content.article
	 * @param  string    $routerName        component router of the form Class::Method
	 *
	 * @return string   $link              URL link to pass to JRouter
	 */
	public static function getItemRoute($contentItemId, $contentItemAlias, $contentCatId, $language, $typeAlias, $routerName)
	{
		$link = '';
		$explodedAlias = explode('.', $typeAlias);
		$explodedRouter = explode('::', $routerName);
		if (file_exists ($routerFile = JPATH_BASE . '/components/' . $explodedAlias[0] . '/helpers/route.php'))
		{
			JLoader::register($explodedRouter[0], $routerFile);
			$routerClass = $explodedRouter[0];
			$routerMethod = $explodedRouter[1];
			if (class_exists($routerClass) && method_exists($routerClass, $routerMethod))
			{
				if ($routerMethod == 'getCategoryRoute')
				{
					$link = $routerClass::$routerMethod($contentItemId, $language);
				}
				else
				{
					$link = $routerClass::$routerMethod($contentItemId . ':' . $contentItemAlias, $contentCatId, $language);
				}
			}
		}
		if ($link == '')
		{
			$link = self::getRoute($contentItemId, $typeAlias, $link, $language, $contentCatId);
		}
		return $link;
	}

	/**
	 * Method to find the item in the menu structure
	 *
	 * @param   array  $needles  Array of lookup values
	 *
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	protected static function findItem($needles = array())
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$extension = $item->query['option'];
			$component = JComponentHelper::getComponent($extension);

			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}
					if (isset($item->query['id']))
					{
						if (is_array($item->query['id']))
						{
							$item->query['id'] = $item->query['id'][0];
						}

						/*
						 * Here it will become a bit tricky
						 * $language != * can override existing entries
						 * $language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		$active = $menus->getActive();
		if ($active && $active->component == $item->query['id'] && ($active->language == '*' || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
