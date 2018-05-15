<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

/**
 * Csp table.
 *
 * @since  __DEPLOY_VERSION__
 */
class Csp extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__csp', 'id', $db);
	}
}
