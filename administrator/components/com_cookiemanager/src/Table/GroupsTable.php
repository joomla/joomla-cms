<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cookiemanager\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Cookie manager groups table
 *
 * @since   __DEPLOY_VERSION__
 */
class GroupsTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cookiemanager', 'id', $db);
	}
}
