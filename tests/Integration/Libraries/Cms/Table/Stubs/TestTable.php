<?php
/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Integration\Libraries\Cms\Table\Stubs;

use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Table\Table;

/**
 * TestTable for common behaviour across integration tests
 *
 * @since   4.0.0
 */
class TestTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   string   DatabaseDriver  $db          Database driver object.
	 * @param   string   DatabaseDriver  $dispatcher  Database driver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct($db, $dispatcher = null)
	{
		parent::__construct('#__testtable', array('id1', 'id2'), $db, $dispatcher);
	}
}
