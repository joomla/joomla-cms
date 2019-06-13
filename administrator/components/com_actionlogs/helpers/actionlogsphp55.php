<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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

		// Header row
		yield array('Id', 'Message', 'Date', 'Extension', 'User', 'Ip');

		foreach ($data as $log)
		{
			$extension = strtok($log->extension, '.');

			ActionlogsHelper::loadTranslationFiles($extension);

			yield array(
				'id'         => $log->id,
				'message'    => strip_tags(ActionlogsHelper::getHumanReadableLogMessage($log, false)),
				'date'       => (new Date($log->log_date, new DateTimeZone('UTC')))->format('Y-m-d H:i:s T'),
				'extension'  => Text::_($extension),
				'name'       => $log->name,
				'ip_address' => Text::_($log->ip_address),
			);
		}
	}
}
