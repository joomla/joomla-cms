<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML utility class for building a dropdown menu
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.2
 */
abstract class JHtmlActionsDropdown
{
	/**
	 * @var    string  HTML markup for the dropdown list
	 * @since  3.2
	 */
	protected static $dropDownList = null;

	/**
	 * Method to render current dropdown menu
	 *
	 * @return  string  HTML markup for the dropdown list
	 *
	 * @since   3.2
	 */
	public static function render($item = '')
	{
		$html = '<button data-toggle="dropdown" class="dropdown-toggle btn btn-micro">'
			. '<span class="caret"></span>';

		if ($item)
		{
			$html .= '<span class="element-invisible">' . JText::sprintf('JACTIONS', $item) . '</span>';
		}

		$html .= '</button>'
			. '<ul class="dropdown-menu">'
			. static::$dropDownList
			. '</ul></div>';

		static::$dropDownList = null;

		return $html;
	}

	/**
	 * Append a publish item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function publish($checkboxId, $prefix = '')
	{
		$task = $prefix . 'publish';
		static::addCustomItem(JText::_('JTOOLBAR_PUBLISH'), 'publish', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append an unpublish item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function unpublish($checkboxId, $prefix = '')
	{
		$task = $prefix . 'unpublish';
		static::addCustomItem(JText::_('JTOOLBAR_UNPUBLISH'), 'unpublish', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append a feature item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function feature($checkboxId, $prefix = '')
	{
		$task = $prefix . 'featured';
		static::addCustomItem(JText::_('JFEATURE'), 'featured', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append an unfeature item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function unfeature($checkboxId, $prefix = '')
	{
		$task = $prefix . 'unfeatured';
		static::addCustomItem(JText::_('JUNFEATURE'), 'unfeatured', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append an archive item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function archive($checkboxId, $prefix = '')
	{
		$task = $prefix . 'archive';
		static::addCustomItem(JText::_('JTOOLBAR_ARCHIVE'), 'archive', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append an unarchive item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function unarchive($checkboxId, $prefix = '')
	{
		$task = $prefix . 'unpublish';
		static::addCustomItem(JText::_('JTOOLBAR_UNARCHIVE'), 'unarchive', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append a trash item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function trash($checkboxId, $prefix = '')
	{
		$task = $prefix . 'trash';
		static::addCustomItem(JText::_('JTOOLBAR_TRASH'), 'trash', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append an untrash item to the current dropdown menu
	 *
	 * @param   string  $checkboxId  ID of corresponding checkbox of the record
	 * @param   string  $prefix      The task prefix
	 *
	 * @since   3.2
	 */
	public static function untrash($checkboxId, $prefix = '')
	{
		$task = $prefix . 'publish';
		static::addCustomItem(JText::_('JTOOLBAR_UNTRASH'), 'trash', null, 'onclick="contextAction(\'' . $checkboxId . '\', \'' . $task . '\')"');
	}

	/**
	 * Append a custom item to current dropdown menu
	 *
	 * @param   string   $label           The label of item
	 * @param   string   $icon            The icon classname
	 * @param   string   $link            The link of item
	 * @param   string   $linkAttributes  Custom link attributes
	 *
	 * @since   3.2
	 */
	public static function addCustomItem($label, $icon = '', $link = 'javascript://', $linkAttributes = '')
	{
		$icon = $icon ? '<span class="icon-' . $icon . '"></span> ' : '';
		static::$dropDownList .= '<li>'
			. '<a  href = "' . $link . '" ' . $linkAttributes . ' >'
			. $icon
			. $label
			. '</a>'
			. '</li>';
	}
}
