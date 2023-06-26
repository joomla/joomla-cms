<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Adapter;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\LegacyErrorHandlingTrait;
use Joomla\CMS\Object\LegacyPropertyManagementTrait;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Adapter Instance Class
 *
 * @since       1.6
 * @deprecated  4.3 will be removed in 6.0
 *              Will be removed without replacement
 */
class AdapterInstance
{
    use LegacyErrorHandlingTrait;
    use LegacyPropertyManagementTrait;

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
    public function __construct(Adapter $parent, DatabaseDriver $db, array $options = [])
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
