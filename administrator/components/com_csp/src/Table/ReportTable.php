<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

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
	 * @param   DatabaseInterface  $db  Database driver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(DatabaseInterface $db)
	{
		parent::__construct('#__csp', 'id', $db);
	}

	/**
	 * Stores a report.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function store($updateNulls = true)
	{
		$date   = Factory::getDate()->toSql();

		if (!$this->id)
		{
			$this->created = $date;
		}

		$this->modified = $date;

		return parent::store($updateNulls);
	}
}
