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
				JArrayHelper::toInteger($config['filter.published']);
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
	public static function ajaxfield($selector='#jform_tags', $allowCustom = true)
	{
		// Tags field ajax
		$chosenAjaxSettings = new JRegistry(
			array(
				'selector'    => $selector,
				'type'        => 'GET',
				'url'         => JUri::root() . 'index.php?option=com_tags&task=tags.searchAjax',
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

						var customTagPrefix = '#new#';

						// Method to add tags pressing enter
						$('" . $selector . "_chzn input').keydown(function(event) {

							// Tag is greater than 3 chars and enter pressed
							if (this.value.length >= 3 && (event.which === 13 || event.which === 188)) {

								// Search an highlighted result
								var highlighted = $('" . $selector . "_chzn').find('li.active-result.highlighted').first();

								// Add the highlighted option
								if (event.which === 13 && highlighted.text() !== '')
								{
									// Extra check. If we have added a custom tag with this text remove it
									var customOptionValue = customTagPrefix + highlighted.text();
									$('" . $selector . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

									// Select the highlighted result
									var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == highlighted.text(); });
									tagOption.attr('selected', 'selected');
								}
								// Add the custom tag option
								else
								{
									var customTag = this.value;

									// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
									var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == customTag; });
									if (tagOption.text() !== '')
									{
										tagOption.attr('selected', 'selected');
									}
									else
									{
										var option = $('<option>');
										option.text(this.value).val(customTagPrefix + this.value);
										option.attr('selected','selected');

										// Append the option an repopulate the chosen field
										$('" . $selector . "').append(option);
									}
								}

								this.value = '';
								$('" . $selector . "').trigger('liszt:updated');
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
