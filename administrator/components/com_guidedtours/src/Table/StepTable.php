<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Guidedtour_steps table
 *
 * @since __DEPLOY_VERSION__
 */
class StepTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore 
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseDriver $db Database connector object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__guidedtour_steps', 'id', $db);
    }

    /**
     * Overloaded store function
     *
     * @param   boolean $updateNulls True to update fields even if they are null.
     *
     * @return mixed  False on failure, positive integer on success.
     *
     * @see   Table::store()
     * @since __DEPLOY_VERSION__
     */
    public function store($updateNulls = true)
    {
        $date = Factory::getDate();
        $user = Factory::getUser();

        $table = new TourTable($this->getDbo());

        if ($this->id) {
            // Existing item
            $this->modified_by = $user->id;
            $this->modified = $date->toSql();
        } else {
            $this->modified_by = 0;
        }

        if (!(int) $this->created) {
            $this->created = $date->toSql();
        }

        if (empty($this->created_by)) {
            $this->created_by = $user->id;
        }

        if (!(int) $this->modified) {
            $this->modified = $this->created;
        }

        if (empty($this->modified_by)) {
            $this->modified_by = $this->created_by;
        }

        return parent::store($updateNulls);
    }
}
