<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\TableInterface;

/**
 * Interface for a versionable Table class
 *
 * @since  3.10.0
 */
interface VersionableTableInterface extends TableInterface
{
	/**
	 * Get the type alias for the history table
	 *
	 * The type alias generally is the internal component name with the
	 * content type. Ex.: com_content.article
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   3.10.0
	 */
	public function getTypeAlias();
}
