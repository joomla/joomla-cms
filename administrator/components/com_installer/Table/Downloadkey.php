<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Downloadkey Table class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Downloadkey extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @see     \JTable::check
	 * @since   __DEPLOY_VERSION__
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
		if (trim($this->location) == '')
		{
			$this->setError(\JText::_('COM_INSTALLER_UPDATESITE_EDIT_VALID_NAME'));

			return false;
		}

		return true;
	}
}
