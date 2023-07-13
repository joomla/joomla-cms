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
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tours table class.
 *
 * @since 4.3.0
 */
class TourTable extends Table implements CurrentUserInterface, TaggableTableInterface
{
    use CurrentUserTrait;
    use TaggableTableTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.3.0
     */
    protected $_supportNullValue = true;

    /**
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     * @since  4.3.0
     */
    protected $_jsonEncode = ['extensions'];

    /**
     * Constructor
     *
     * @param   DatabaseDriver $db Database connector object
     *
     * @since   4.3.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_guidedtours.tour';

        parent::__construct('#__guidedtours', 'id', $db);
    }

    /**
     * Overloaded bind function.
     *
     * @param   array   $array   named array
     * @param   string  $ignore  An optional array or space separated list of properties
     *                           to ignore while binding.
     *
     * @return  mixed   Null if operation was satisfactory, otherwise returns an error
     *
     * @see     Table::bind()
     * @since   __DEPLOY_VERSION__
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry        = new Registry($array['params']);
            $array['params'] = (string) $registry;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Stores a tour.
     *
     * @param   boolean $updateNulls True to update extensions even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   4.3.0
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

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

        if (empty($this->extensions)) {
            $this->extensions = ["*"];
        }

        return parent::store($updateNulls);
    }

    /**
     * Get the type alias for UCM features
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
