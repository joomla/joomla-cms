<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

/**
 * UCM map table
 *
 * @since  3.1
 */
class Ucm extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_base', 'ucm_id', $db);
	}
}
