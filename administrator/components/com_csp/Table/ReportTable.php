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
 * @since  4.0.0
 */
class ReportTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database driver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__csp', 'id', $db);
	}
}
