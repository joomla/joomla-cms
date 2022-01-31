<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Adapter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\Database\DatabaseDriver;

/**
 * Adapter Instance Class
 *
 * @since       1.6
 * @deprecated  5.0 Will be removed without replacement
 */
class AdapterInstance extends CMSObject
{
	/**
	 * Parent
	 *
	 * @var    Adapter
	 * @since  1.6
	 */
	protected $parent = null;

	/**
	 * Database
	 *
	 * @var    DatabaseDriver
	 * @since  1.6
	 */
	protected $db = null;

	/**
	 * Constructor
	 *
	 * @param   Adapter         $parent   Parent object
	 * @param   DatabaseDriver  $db       Database object
	 * @param   array           $options  Configuration Options
	 *
	 * @since   1.6
	 */
	public function __construct(Adapter $parent, DatabaseDriver $db, array $options = array())
	{
		// Set the properties from the options array that is passed in
		$this->setProperties($options);

		// Set the parent and db in case $options for some reason overrides it.
		$this->parent = $parent;

		// Pull in the global dbo in case something happened to it.
		$this->db = $db ?: Factory::getDbo();
	}

	/**
	 * Retrieves the parent object
	 *
	 * @return  Adapter
	 *
	 * @since   1.6
	 */
	public function getParent()
	{
		return $this->parent;
	}
}
