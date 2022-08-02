<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Adapter;

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
    public function __construct(protected Adapter $parent, DatabaseDriver $db, array $options = [])
    {
        // Set the properties from the options array that is passed in
        $this->setProperties($options);

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
