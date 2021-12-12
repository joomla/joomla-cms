<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;

/**
 * Update site table
 * Stores the update sites for extensions
 *
 * @since  3.4
 */
class UpdateSite extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database driver object.
	 *
	 * @since   3.4
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__update_sites', 'update_site_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True if the object is ok
	 *
	 * @see     Table::check()
	 * @since   3.4
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Check for valid name
		if (trim($this->name) == '' || trim($this->location) == '')
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

			return false;
		}

		return true;
	}
}
