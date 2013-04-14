<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * UCM map table
 *
 * @package     Joomla.Libraries
 * @subpackage  Table
 * @since       3.1
 */
class JTableUcm extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_base', 'ucm_id', $db);
	}
}
