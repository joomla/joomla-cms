<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\Mixin;

defined('_JEXEC') || die;

/**
 * Trait for dealing with data stored as JSON-encoded strings
 */
trait JsonData
{
	/**
	 * Converts the loaded JSON string into an array
	 *
	 * @param   string  $value  The JSON string
	 *
	 * @return  array  The data
	 */
	protected function getAttributeForJson($value)
	{
		if (is_array($value))
		{
			return $value;
		}

		if (empty($value))
		{
			return [];
		}

		$value = json_decode($value, true);

		if (empty($value))
		{
			return [];
		}

		return $value;
	}

	/**
	 * Converts and array into a JSON string
	 *
	 * @param   array  $value  The data
	 *
	 * @return  string  The JSON string
	 */
	protected function setAttributeForJson($value)
	{
		if (!is_array($value))
		{
			return $value;
		}

		$value = json_encode($value);

		return $value;
	}
}
