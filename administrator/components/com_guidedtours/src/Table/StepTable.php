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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Step table class.
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
     * Stores a step.
     *
     * @param   boolean $updateNulls True to update fields even if they are null.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since __DEPLOY_VERSION__
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = Factory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        return parent::store($updateNulls);
    }

    /**
     * Returns the asset name of the entry as it appears in the {@see Asset} table.
     *
     * @return  string  The asset name.
     *
     * @since   4.1.0
     */
    // phpcs:ignore
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_guidedtours.step.' . (int) $this->$k;
    }
}
