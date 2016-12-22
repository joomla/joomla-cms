<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for tags
 *
 * @since  3.1
 */
abstract class JHtmlTag
{
	/**
	 * Cached array of the tag items.
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected static $items = array();

	/**
	 * Returns an array of tags.
	 *
	 * @param   array  $config  An array of configuration options. By default, only
	 *                          published and unpublished categories are returned.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public static function options($config = array('filter.published' => array(0, 1)))
	{
		$hash = md5(serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id, a.title, a.level')
				->from('#__tags AS a')
				->where('a.parent_id > 0');

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			// Filter on the language
			if (isset($config['filter.language']))
			{
				if (is_string($config['filter.language']))
				{
					$query->where('a.language = ' . $db->quote($config['filter.language']));
				}
				elseif (is_array($config['filter.language']))
				{
					foreach ($config['filter.language'] as &$language)
					{
						$language = $db->quote($language);
					}

					$query->where('a.language IN (' . implode(',', $config['filter.language']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				static::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		return static::$items[$hash];
	}

	/**
	 * Returns an array of tags.
	 *
	 * @param   array  $config  An array of configuration options. By default, only published and unpublished tags are returned.
	 *
	 * @return  array  Tag data
	 *
	 * @since   3.1
	 */
	public static function tags($config = array('filter.published' => array(0, 1)))
	{
		$hash = md5(serialize($config));
		$config = (array) $config;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.parent_id')
			->from('#__tags AS a')
			->where('a.parent_id > 0');

		// Filter on the published state
		if (isset($config['filter.published']))
		{
			if (is_numeric($config['filter.published']))
			{
				$query->where('a.published = ' . (int) $config['filter.published']);
			}
			elseif (is_array($config['filter.published']))
			{
				$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
				$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
			}
		}

		$query->order('a.lft');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Assemble the list options.
		static::$items[$hash] = array();

		foreach ($items as &$item)
		{
			$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;
			static::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
		}

		return static::$items[$hash];
	}

	/**
	 * This is just a proxy for the formbehavior.ajaxchosen method
	 *
	 * @param   string   $selector     DOM id of the tag field
	 * @param   boolean  $allowCustom  Flag to allow custom values
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function ajaxfield($selector = '#jform_tags', $allowCustom = true)
	{
		// Get the component parameters
		$params = JComponentHelper::getParams('com_tags');
		$minTermLength = (int) $params->get('min_term_length', 3);

		JText::script('JGLOBAL_KEEP_TYPING');
		JText::script('JGLOBAL_LOOKING_FOR');

		// Include scripts
		JHtml::_('behavior.core');
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/legacy/ajax-chosen.min.js', false, true, false, false, JDEBUG);

		JFactory::getDocument()->addScriptOptions(
			'ajax-chosen',
			array(
				'url'            => JUri::root() . 'index.php?option=com_tags&task=tags.searchAjax',
				'debug'          => JDEBUG,
				'selector'       => $selector,
				'type'           => 'GET',
				'dataType'       => 'json',
				'jsonTermKey'    => 'like',
				'afterTypeDelay' => 500,
				'minTermLength'  => $minTermLength
			)
		);

		// Allow custom values ?
		if ($allowCustom)
		{
			JHtml::_('script', 'system/fields/tag.min.js', false, true, false, false, JDEBUG);
			JFactory::getDocument()->addScriptOptions(
				'field-tag-custom',
				array(
					'minTermLength' => $minTermLength,
					'selector'      => $selector,
					'allowCustom'   => JFactory::getUser()->authorise('core.create', 'com_tags') ? $allowCustom : false,
				)
			);
		}
	}
}
