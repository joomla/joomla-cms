<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Interface for a database model.
 *
 * @since  4.0.0
 */
interface DatabaseModelInterface
{
	/**
	 * Method to get the database driver object.
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   4.0.0
	 */
	public function getDbo();
}
