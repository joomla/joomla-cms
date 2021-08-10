<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Routing class from com_tags
 *
 * @since  3.3
 */
class Router extends RouterBase
{
	/**
	 * Lookup array of the menu items
	 *
	 * @var   array
	 * @since 4.1
	 */
	protected $lookup = array();

  /**
	 * The db
	 *
	 * @var DatabaseInterface
	 *
	 * @since  4.0
	 */
	private $db;

	/**
	 * Tags Component router constructor
	 *
	 * @param   SiteApplication           $app              The application object
	 * @param   AbstractMenu              $menu             The menu object to work with
	 * @param   CategoryFactoryInterface  $categoryFactory  The category object
	 * @param   DatabaseInterface         $db               The database object
	 *
	 * @since  4.0.0
	 */
	public function __construct(SiteApplication $app, AbstractMenu $menu, ?CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		parent::__construct($app, $menu);

		$this->db = $db;

		$this->buildLookup();
	}

	/**
	 * Method to preprocess a URL
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   4.0
	 */
	public function preprocess($query)
	{
		$active = $this->menu->getActive();

		/**
		 * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
		 * menu item then we just use the supplied menu item and continue
		 */
		if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id))
		{
			return $query;
		}

		// Get query language
		$language = isset($query['lang']) ? $query['lang'] : '*';

		// Set the language to the current one when multilang is enabled and item is tagged to ALL
		if (Multilanguage::isEnabled() && $language === '*')
		{
			$language = $this->app->get('language');
		}

		if (!isset($this->lookup[$language]))
		{
			$this->buildLookup($language);
		}

		// Check if the active menu item matches the requested query
		if ($active !== null && isset($query['Itemid']))
		{
			// Check if active->query and supplied query are the same
			$match = true;

			foreach ($active->query as $k => $v)
			{
				if (isset($query[$k]) && $v !== $query[$k])
				{
					// Compare again without alias
					if (\is_string($v) && $v == current(explode(':', $query[$k], 2)))
					{
						continue;
					}

					$match = false;
					break;
				}
			}

			if ($match)
			{
				// Just use the supplied menu item
				return $query;
			}
		}

		$id = '';

		if (isset($query['id']))
		{
			if (is_array($query['id']))
			{
				$ids = ArrayHelper::toInteger($query['id']);
				$id = implode(':', $ids);
			}
			else
			{
				$id = (int) $query['id'];
			}
		}

		$view = $query['view'];
		$layout = isset($query['layout']) && $query['layout'] !== 'default' ? ':' . $query['layout'] : '';

		foreach (['tag', 'tags'] as $view)
		{
			if (isset($this->lookup[$language][$view . $layout][$id]))
			{
				$query['Itemid'] = $this->lookup[$language][$view . $layout][$id];

				return $query;
			}

			if (isset($this->lookup[$language][$view][$id]))
			{
				$query['Itemid'] = $this->lookup[$language][$view][$id];

				return $query;
			}
		}

		if (isset($this->lookup[$language][$view]['']))
		{
			$query['Itemid'] = $this->lookup[$language][$view][''];

			return $query;
		}

		// Check if the active menuitem matches the requested language
		if ($active && $active->component === 'com_tags'
			&& ($language === '*' || \in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
		{
			$query['Itemid'] = $active->id;

			return $query;
		}

		// If not found, return language specific home link
		$default = $this->menu->getDefault($language);

		if (!empty($default->id))
		{
			$query['Itemid'] = $default->id;
		}

		return $query;
	}

	/**
	 * Build the route for the com_tags component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
		$mId   = empty($menuItem->query['id']) ? null : $menuItem->query['id'];

		if (is_array($mId))
		{
			$mId = ArrayHelper::toInteger($mId);
		}

		$view = '';

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']))
			{
				$segments[] = $view;
			}

			unset($query['view']);
		}

		// Are we dealing with a tag that is attached to a menu item?
		if ($mView == $view && isset($query['id']) && $mId == $query['id'])
		{
			unset($query['id']);

			return $segments;
		}

		if ($view === 'tag')
		{
			$notActiveTag = is_array($mId) ? (count($mId) > 1 || $mId[0] != (int) $query['id']) : ($mId != (int) $query['id']);

			if ($notActiveTag || $mView != $view)
			{
				// ID in com_tags can be either an integer, a string or an array of IDs
				$id = is_array($query['id']) ? implode(',', $query['id']) : $query['id'];
				$segments[] = $id;
			}

			unset($query['id']);
		}

		if (isset($query['layout']))
		{
			if ((!empty($query['Itemid']) && isset($menuItem->query['layout'])
				&& $query['layout'] == $menuItem->query['layout'])
				|| $query['layout'] === 'default')
			{
				unset($query['layout']);
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
			$position     = strpos($segments[$i], '-');

			if ($position)
			{
				// Remove id from segment
				$segments[$i] = substr($segments[$i], $position + 1);
			}
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item = $this->menu->getActive();

		// Count route segments
		$count = count($segments);

		// Standard routing for tags.
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id']   = $this->fixSegment($segments[$count - 1]);
			unset($segments[0]);
			unset($segments[$count - 1]);

			return $vars;
		}

		$vars['id'] = $this->fixSegment($segments[0]);
		$vars['view'] = 'tag';
		unset($segments[0]);

		return $vars;
	}

	/**
	 * Try to add missing id to segment
	 *
	 * @param   string  $segment  One piece of segment of the URL to parse
	 *
	 * @return  string  The segment with founded id
	 *
	 * @since   3.7
	 */
	protected function fixSegment($segment)
	{
		// Try to find tag id
		$alias = str_replace(':', '-', $segment);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__tags'))
			->where($this->db->quoteName('alias') . ' = :alias')
			->bind(':alias', $alias);

		$id = $this->db->setQuery($query)->loadResult();

		if ($id)
		{
			$segment = "$id:$alias";
		}

		return $segment;
	}

	/**
	 * Method to build the lookup array
	 *
	 * @param   string  $language  The language that the lookup should be built up for
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function buildLookup($language = '*')
	{
		// Prepare the reverse lookup array.
		if (!isset($this->lookup[$language]))
		{
			$this->lookup[$language] = array();

			$component  = ComponentHelper::getComponent('com_tags');
			$views = ['tags', 'tag'];

			$attributes = array('component_id');
			$values     = array((int) $component->id);

			$attributes[] = 'language';
			$values[]     = array($language, '*');

			$items = $this->menu->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query['view']) && in_array($item->query['view'], $views))
				{
					$view = $item->query['view'];

					$layout = '';

					if (isset($item->query['layout']))
					{
						$layout = ':' . $item->query['layout'];
					}

					if (!isset($this->lookup[$language][$view . $layout]))
					{
						$this->lookup[$language][$view . $layout] = array();
					}

					if (!isset($this->lookup[$language][$view]))
					{
						$this->lookup[$language][$view] = array();
					}

					$id = '';

					if (isset($item->query['id']))
					{
						if (is_array($item->query['id']))
						{
							$id = implode(':', $item->query['id']);
						}
						else
						{
							$id = (string) $item->query['id'];
						}
					}

					/**
					 * Here it will become a bit tricky
					 * language != * can override existing entries
					 * language == * cannot override existing entries
					 */
					if (!isset($this->lookup[$language][$view . $layout][$id]) || $item->language !== '*')
					{
						$this->lookup[$language][$view . $layout][$id] = $item->id;
						$this->lookup[$language][$view][$id] = $item->id;
					}
				}
			}
		}
	}
}
