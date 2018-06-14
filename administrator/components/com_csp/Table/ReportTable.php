<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Csp\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Report table
 *
 * @since  __DEPLOY_VERSION__
 */
class ReportTable extends Table
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
