<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Downloadkey Table class.
 *
 * @since  4.0.0
 */
class UpdatesiteTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since   4.0.0
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = 'com_installer.downloadkey';

		parent::__construct('#__update_sites', 'update_site_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check
	 * @since   4.0.0
	 */
	public function check()
	{
		parent::check();

		// Check for valid name
		if (trim($this->get('location')) === '')
		{
			$this->setError(Text::_('COM_INSTALLER_UPDATESITE_EDIT_VALID_NAME'));

			return false;
		}

		return true;
	}
}
