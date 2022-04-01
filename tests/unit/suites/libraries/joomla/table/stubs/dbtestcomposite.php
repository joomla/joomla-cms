<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * DbTestComposite table connector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       3.0.0
 */
class TableDbTestComposite extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   3.0.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__dbtest_composite', array('id1', 'id2'), $db);
	}
}
