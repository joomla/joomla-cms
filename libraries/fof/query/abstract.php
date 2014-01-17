<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * FrameworkOnFramework query base class; for compatibility purposes
 *
 * @package     FrameworkOnFramework
 * @since       2.1
 * @deprecated  2.1
 */
abstract class FOFQueryAbstract
{
	/**
	 * Returns a new database query class
	 *
	 * @param   JDatabaseDriver  $db  The DB driver which will provide us with a query object
	 *
	 * @return FOFQueryAbstract
	 */
	public static function &getNew($db = null)
	{
		JLog::add('FOFQueryAbstract is deprecated. Use JDatabaseQuery instead.', JLog::WARNING, 'deprecated');

		if (is_null($db))
		{
			$ret = JFactory::getDbo()->getQuery(true);
		}
		else
		{
			$ret = $db->getQuery(true);
		}

		return $ret;
	}
}
