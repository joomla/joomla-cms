<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Userlogs Table class
 *
 * @since  __DEPLOY_VERSION__
 */
class JTableUserlogs extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__user_logs', 'id', $db);
	}
}
