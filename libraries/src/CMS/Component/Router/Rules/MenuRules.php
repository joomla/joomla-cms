<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router\Rules;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;

/**
 * Rule to identify the right Itemid for a view in a component
 *
 * @since  3.4
 */
class MenuRules implements RulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var   RouterView
	 * @since 3.4
	 */
	protected $router;

	/**
	 * Lookup array of the menu items
	 *
	 * @var   array
	 * @since 3.4
	 */
	protected $lookup = array();

	/**
	 * Class constructor.
	 *
	 * @param   RouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(RouterView $router)
	{
		$this->router = $router;

		$this->buildLookup();
	}

	/**
	 * Finds the right Itemid for this query
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function preprocess(&$query)
	{
		/**
		 * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
		 * menu item then we just use the supplied menu item and continue
		 */
		if (isset($query['Itemid'])
			&& (($this->router->menu->getActive() && $query['Itemid'] != $this->router->menu->getActive()->id)
			|| ($this->router->menu->getActive() === null)))
		{
			return;
		}

		$language = '*';
		if (isset($query['lang']))
		{
			$language = $query['lang'];

			if (!isset($this->lookup[$query['lang']]))
			{
				$this->buildLookup($query['lang']);
			}
		}

		$needles = $this->router->getPath($query);

		$layout = '';

		if (isset($query['layout']))
		{
			$layout = ':' . $query['layout'];
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset($this->lookup[$language][$view . $layout]))
				{
					if (is_bool($ids))
					{
						$query['Itemid'] = $this->lookup[$language][$view . $layout];
						return;
					}
					foreach ($ids as $id => $segment)
					{
						if (isset($this->lookup[$language][$view . $layout][(int) $id]))
						{
							$query['Itemid'] = $this->lookup[$language][$view . $layout][(int) $id];
							return;
						}

						if (isset($this->lookup[$language][$view][(int) $id]))
						{
							$query['Itemid'] = $this->lookup[$language][$view][(int) $id];
							return;
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $this->router->menu->getActive();

		if ($active && $active->component === 'com_' . $this->router->getName()
			&& ($language === '*' || in_array($active->language, array('*', $language)) || !\JLanguageMultilang::isEnabled()))
		{
			$query['Itemid'] = $active->id;
			return;
		}

		// If not found, return language specific home link
		$default = $this->router->menu->getDefault($language);

		if (!empty($default->id))
		{
			$query['Itemid'] = $default->id;
		}
	}

	/**
	 * Method to build the lookup array
	 *
	 * @param   string  $language  The language that the lookup should be built up for
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function buildLookup($language = '*')
	{
		// Prepare the reverse lookup array.
		if (!isset($this->lookup[$language]))
		{
			$this->lookup[$language] = array();

			$component  = ComponentHelper::getComponent('com_' . $this->router->getName());
			$views = $this->router->getViews();

			$attributes = array('component_id');
			$values     = array((int) $component->id);

			$attributes[] = 'language';
			$values[]     = array($language, '*');

			$items = $this->router->menu->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					$layout = '';

					if (isset($item->query['layout']))
					{
						$layout = ':' . $item->query['layout'];
					}

					if ($views[$view]->key)
					{
						if (!isset($this->lookup[$language][$view . $layout]))
						{
							$this->lookup[$language][$view . $layout] = array();
						}

						if (!isset($this->lookup[$language][$view]))
						{
							$this->lookup[$language][$view] = array();
						}

						// If menuitem has no key set, we assume 0.
						if (!isset($item->query[$views[$view]->key]))
						{
							$item->query[$views[$view]->key] = 0;
						}

						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset($this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]]) || $item->language !== '*')
						{
							$this->lookup[$language][$view . $layout][$item->query[$views[$view]->key]] = $item->id;
							$this->lookup[$language][$view][$item->query[$views[$view]->key]] = $item->id;
						}
					}
					else
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset($this->lookup[$language][$view . $layout]) || $item->language !== '*')
						{
							$this->lookup[$language][$view . $layout] = $item->id;
							$this->lookup[$language][$view] = $item->id;
						}
					}
				}
			}
		}
	}

	/**
	 * Dummymethod to fullfill the interface requirements
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @codeCoverageIgnore
	 */
	public function parse(&$segments, &$vars)
	{
	}

	/**
	 * Dummymethod to fullfill the interface requirements
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @codeCoverageIgnore
	 */
	public function build(&$query, &$segments)
	{
	}
}
