<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Basic routing class
 *
 * @package     Joomla.Libraries
 * @subpackage  Component
 * @since       3.3
 */
abstract class JComponentRouterBasic
{
	protected $lookup = array();

	protected $lang_lookup = array();
	
	protected $name;

	/**
	 * Constructor
	 *
	 * @param   string  $component  Component name without the com_ prefix this router should react upon
	 *
	 * @since   3.3
	 */
	public function __construct()
	{
		$this->buildLookupTables('*');
	}

	/**
	 * Preprocess method
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function preprocess($query)
	{
		$needles = array();

		if (isset($query['language']) && $query['language'] != "*" && JLanguageMultilang::isEnabled() && isset($this->lang_lookup[$query['language']]))
		{
			$query['lang'] = $this->lang_lookup[$query['language']];
			$needles['language'] = $query['lang'];
		}
		unset($query['language']);
		
		if (isset($query['view']))
		{
			if(isset($query['id']))
			{
				$needles[$query['view']] = array($query['id']);
				$category = null;
				if ($query['view'] == 'category' || $query['view'] == 'categories')
				{
					$category = JCategories::getInstance($this->getName())->get($query['id']);
				}
				elseif (isset($query['catid']))
				{
					$category = JCategories::getInstance($this->getName())->get($query['catid']);
				}
				
				if ($category instanceof JCategoryNode)
				{
					$needles['category'] = array_reverse($category->getPath());
					$needles['categories'] = $needles['category'];
				}
			}
			$itemid = $this->getItemid($needles);
			
			if ($itemid > 0)
			{
				$query['Itemid'] = $itemid;
			}
		}

		return $query;
	}
	
	protected function buildLookupTables($language)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.sef AS sef')
			->select('a.lang_code AS lang_code')
			->from('#__languages AS a');
		$db->setQuery($query);

		$langs = $db->loadObjectList();
		foreach ($langs as $lang)
		{
			$this->lang_lookup[$lang->lang_code] = $lang->sef;
		}

		$component	= JComponentHelper::getComponent('com_' . strtolower($this->getName()));

		$attributes = array('component_id', 'language');
		$values = array($component->id, '*');

		$items		= $menus->getItems($attributes, $values);

		foreach ($items as $item)
		{
			if (isset($item->query) && isset($item->query['view']))
			{
				$view = $item->query['view'];
				if (!isset($this->lookup[$language][$view]))
				{
					$this->lookup[$language][$view] = array();
				}
				if (isset($item->query['id']))
				{
					// here it will become a bit tricky
					// language != * can override existing entries
					// language == * cannot override existing entries
					if (!isset($this->lookup[$language][$view][$item->query['id']]) || $item->language != '*')
					{
						$this->lookup[$language][$view][$item->query['id']] = $item->id;
					}
				}
			}
		}
	}
	
	protected function getItemid($needles)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
		$language	= isset($needles['language']) ? $needles['language'] : '*';

		if(!isset($this->lookup[$language]))
		{
			$this->buildLookupTables($language);
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset($this->lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset($this->lookup[$language][$view][(int) $id]))
						{
							return $this->lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();
		if ($active && $active->component == 'com_' . strtolower($this->getName()) && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);
		return !empty($default->id) ? $default->id : null;
	}
	
	/**
	 * Method to get the routers component name
	 *
	 * @return  string  The name of the routers component
	 *
	 * @since   3.3
	 * @throws  Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;
			if (!preg_match('/(.*)Router/i', get_class($this), $r))
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ROUTER_GET_NAME'), 500);
			}
			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}
}
