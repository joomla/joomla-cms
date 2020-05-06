<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * Languages component helper.
 *
 * @since  1.6
 */
class LanguagesHelper
{
	/**
	 * Filter method for language keys.
	 * This method will be called by \JForm while filtering the form data.
	 *
	 * @param   string  $value  The language key to filter.
	 *
	 * @return  string	The filtered language key.
	 *
	 * @since		2.5
	 */
	public static function filterKey($value)
	{
		$filter = \JFilterInput::getInstance(null, null, 1, 1);

		return strtoupper($filter->clean($value, 'cmd'));
	}

	/**
	 * Filter method for language strings.
	 * This method will be called by \JForm while filtering the form data.
	 *
	 * @param   string  $value  The language string to filter.
	 *
	 * @return  string	The filtered language string.
	 *
	 * @since		2.5
	 */
	public static function filterText($value)
	{
		$filter = \JFilterInput::getInstance(null, null, 1, 1);

		return $filter->clean($value);
	}
}
