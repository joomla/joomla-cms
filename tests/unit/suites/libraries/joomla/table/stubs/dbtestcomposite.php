<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * DbTestComposite table connector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       12.1
 */
class TableDbTestComposite extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   12.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__dbtest_composite', array('id1', 'id2'), $db);
	}
}
