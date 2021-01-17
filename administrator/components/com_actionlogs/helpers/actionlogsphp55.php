<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;

/**
 * Actionlogs component helper for newer PHP versions.
 *
 * This file should only be included in environments running PHP 5.5 or newer and may potentially cause a parse error on older versions.
 *
 * @since       3.9.0
 * @deprecated  Will be inlined back into ActionlogsHelper when PHP 5.5 or newer is the minimum supported PHP version
 * @internal
 */
class ActionlogsHelperPhp55
{
	/**
	 * Array of characters starting a formula
	 *
	 * @var    array
	 * @since  3.9.7
	 */
	private static $characters = array('=', '+', '-', '@');

	/**
	 * Method to convert logs objects array to a Generator for use with a CSV export
	 *
	 * @param   array|Traversable  $data  The logs data objects to be exported
	 *
	 * @return  Generator
	 *
	 * @since   3.9.0
	 * @throws  InvalidArgumentException
	 */
	public static function getCsvAsGenerator($data)
	{
		if (!is_iterable($data))
		{
			throw new InvalidArgumentException(
				sprintf(
					'%s() requires an array or object implementing the Traversable interface, a %s was given.',
					__METHOD__,
					gettype($data) === 'object' ? get_class($data) : gettype($data)
				)
			);
		}

		$disabledText = Text::_('COM_ACTIONLOGS_DISABLED');

		// Header row
		yield array('Id', 'Message', 'Date', 'Extension', 'User', 'Ip');

		foreach ($data as $log)
		{
			$extension = strtok($log->extension, '.');

			ActionlogsHelper::loadTranslationFiles($extension);

			yield array(
				'id'         => $log->id,
				'message'    => self::escapeCsvFormula(strip_tags(ActionlogsHelper::getHumanReadableLogMessage($log, false))),
				'date'       => (new Date($log->log_date, new DateTimeZone('UTC')))->format('Y-m-d H:i:s T'),
				'extension'  => self::escapeCsvFormula(Text::_($extension)),
				'name'       => self::escapeCsvFormula($log->name),
				'ip_address' => self::escapeCsvFormula($log->ip_address === 'COM_ACTIONLOGS_DISABLED' ? $disabledText : $log->ip_address)
			);
		}
	}

	/**
	 * Escapes potential characters that start a formula in a CSV value to prevent injection attacks
	 *
	 * @param   mixed  $value  csv field value
	 *
	 * @return  mixed
	 *
	 * @since   3.9.7
	 */
	protected static function escapeCsvFormula($value)
	{
		if ($value == '')
		{
			return $value;
		}

		if (in_array($value[0], self::$characters, true))
		{
			$value = ' ' . $value;
		}

		return $value;
	}
}
