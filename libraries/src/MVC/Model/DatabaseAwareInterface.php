<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a database.
 *
 * @since  __DEPLOY_VERSION__
 */
interface DatabaseAwareInterface
{
	/**
	 * Set the database to use.
	 *
	 * @param   DatabaseInterface  $db  The database to use.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDbo(DatabaseInterface $db = null): void;
}
