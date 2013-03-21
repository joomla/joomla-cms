<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Utility class for tags
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.1
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

		if (!isset(self::$items[$hash]))
		{
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title, a.level');
			$query->from('#__tags AS a');
			$query->where('a.parent_id > 0');

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					JArrayHelper::toInteger($config['filter.published']);
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
			self::$items[$hash] = array();

			foreach ($items as &$item)
			{
				$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
				$item->title = str_repeat('- ', $repeat) . $item->title;
				self::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
			}
		}

		return self::$items[$hash];
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
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.level, a.parent_id');
		$query->from('#__tags AS a');
		$query->where('a.parent_id > 0');

		// Filter on the published state
		if (isset($config['filter.published']))
		{
			if (is_numeric($config['filter.published']))
			{
				$query->where('a.published = ' . (int) $config['filter.published']);
			}
			elseif (is_array($config['filter.published']))
			{
				JArrayHelper::toInteger($config['filter.published']);
				$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
			}
		}

		$query->order('a.lft');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Assemble the list options.
		self::$items[$hash] = array();

		foreach ($items as &$item)
		{
			$repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;
			self::$items[$hash][] = JHtml::_('select.option', $item->id, $item->title);
		}
		return self::$items[$hash];
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
	public static function ajaxfield($selector='#jform_tags', $allowCustom = true)
	{
		// Tags field ajax
		$chosenAjaxSettings = new JRegistry(
			array(
				'selector'    => $selector,
				'type'        => 'GET',
				'url'         => JURI::root() . 'index.php?option=com_tags&task=tags.searchAjax',
				'dataType'    => 'json',
				'jsonTermKey' => 'like'
			)
		);
		JHtml::_('formbehavior.ajaxchosen', $chosenAjaxSettings);

		// Allow custom values ?
		if ($allowCustom)
		{
			JFactory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {
						// Method to add tags pressing enter
						$('" . $selector . "_chzn input').keydown(function(event) {
							// tag is greater than 3 chars and enter pressed
							if (this.value.length >= 3 && (event.which === 13 || event.which === 188)) {
								// Create the option
								var option = $('<option>');
								option.text(this.value).val('#new#' + this.value);
								option.attr('selected','selected');
								// Add the option an repopulate the chosen field
								$('" . $selector . "').append(option).trigger('liszt:updated');
								this.value = '';
								event.preventDefault();
							}
						});
					});
				})(jQuery);
				"
			);
		}
	}
}
