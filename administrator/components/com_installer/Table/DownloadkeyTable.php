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

use Joomla\CMS\Table\Table;

/**
 * Downloadkey Table class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DownloadkeyTable extends Table
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
}
