<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

/**
 * TUF map table
 *
 * @since  __DEPLOY_VERSION__
 */
class Tuf extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \Joomla\Database\DatabaseDriver  $db  A database connector object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($db)
	{
		parent::__construct('#__tuf_metadata', 'id', $db);
	}
}
