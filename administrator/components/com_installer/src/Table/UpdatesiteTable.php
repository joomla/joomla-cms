<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Table;

\defined('_JEXEC') or die;

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
}
