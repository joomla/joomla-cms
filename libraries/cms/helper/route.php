<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Route Helper
 *
 * A class providing basic routing for urls that are for content types found in
 * the #__content_types table and rows found in the #__ucm_content table.
 *
 * @since  3.1
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
	protected $extension;

	/**
	 * @var    string  Value of the primary key in the content type table
	 * @since  3.1
	 */
	protected $id;

	/**
	 * @var    string  Name of the view for the url
	 * @since  3.1
	 */
	protected $view;

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
	public function getRoute($id, $typealias, $link = '', $language = null, $catid = null)
	{
		$typeExploded = explode('.', $typealias);

		if (isset($typeExploded[1]))
		{
			$this->view = $typeExploded[1];
			$this->extension = $typeExploded[0];
		}
		else
		{
			$this->view = JFactory::getApplication()->input->getString('view');
			$this->extension = JFactory::getApplication()->input->getCmd('option');
		}

		$name = ucfirst(substr_replace($this->extension, '', 0, 4));

		$needles = array();

		if (isset($this->view))
		{
			$needles[$this->view] = array((int) $id);
		}

		if (empty($link))
		{
			// Create the link
			$link = 'index.php?option=' . $this->extension . '&view=' . $this->view . '&id=' . $id;
		}

		if ($catid > 1)
		{
			$categories = JCategories::getInstance($name);

			if ($categories)
			{
				$category = $categories->get((int) $catid);

				if ($category)
				{
					$needles['category'] = array_reverse($category->getPath());
					$needles['categories'] = $needles['category'];
					$link .= '&catid=' . $catid;
				}
			}
		}

		// Deal with languages only if needed
		if (!empty($language) && $language != '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
			$needles['language'] = $language;
		}

		if ($item = $this->findItem($needles))
		{
			$link .= '&Itemid=' . $item;
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
	protected function findItem($needles = array())
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// $this->extension may not be set if coming from a static method, check it
		if (is_null($this->extension))
		{
			$this->extension = $app->input->getCmd('option');
		}

		// Prepare the reverse lookup array.
		if (!isset(static::$lookup[$language]))
		{
			static::$lookup[$language] = array();

			$component = JComponentHelper::getComponent($this->extension);

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(static::$lookup[$language][$view]))
					{
						static::$lookup[$language][$view] = array();
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
						if (!isset(static::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							static::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(static::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(static::$lookup[$language][$view][(int) $id]))
						{
							return static::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		$active = $menus->getActive();

		if ($active && $active->component == $this->extension && ($active->language == '*' || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}

	/**
	 * Fetches the category route
	 *
	 * @param   mixed   $catid      Category ID or JCategoryNode instance
	 * @param   mixed   $language   Language code
	 * @param   string  $extension  Extension to lookup
	 *
	 * @return  string
	 *
	 * @since   3.2
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function getCategoryRoute($catid, $language = 0, $extension = '')
	{
		// Note: $extension is required but has to be an optional argument in the function call due to argument order
		if (empty($extension))
		{
			throw new InvalidArgumentException('$extension is a required argument in JHelperRoute::getCategoryRoute');
		}

		if ($catid instanceof JCategoryNode)
		{
			$id       = $catid->id;
			$category = $catid;
		}
		else
		{
			$extensionName = ucfirst(substr($extension, 4));
			$id            = (int) $catid;
			$category      = JCategories::getInstance($extensionName)->get($id);
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			$link = 'index.php?option=' . $extension . '&view=category&id=' . $id;

			$needles = array(
				'category' => array($id),
			);

			if ($language && $language != '*' && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
				$needles['language'] = $language;
			}

			// Create the link
			if ($category)
			{
				$catids                = array_reverse($category->getPath());
				$needles['category']   = $catids;
				$needles['categories'] = $catids;

			}

			if ($item = static::lookupItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 * Static alias to findItem() used to find the item in the menu structure
	 *
	 * @param   array  $needles  Array of lookup values
	 *
	 * @return  mixed
	 *
	 * @since   3.2
	 */
	protected static function lookupItem($needles = array())
	{
		$instance = new static;

		return $instance->findItem($needles);
	}
}
