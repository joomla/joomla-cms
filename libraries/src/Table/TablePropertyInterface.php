<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

use Joomla\Database\DatabaseDriver;

/**
 * Table property class interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface TablePropertyInterface // SqlDataInterface //TablePropertyInterface
{
	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $value  Value geted from cell BaseData
	 *
	 * @since   4.0.0
	 */
	public function __construct($value = null);

	/**
	 * Gets the date as an SQL datetime string.
	 *
	 * @return  number|string|boolean  The date string in SQL datetime format.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function toSql();
}
