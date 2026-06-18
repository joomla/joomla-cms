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
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Step table class.
 *
 * @since 4.3.0
 */
class StepTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.3.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since  4.3.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__guidedtour_steps', 'id', $db, $dispatcher);
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
     * @since   5.1.0
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
     * Stores a step.
     *
     * @param   boolean $updateNulls True to update fields even if they are null.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since 4.3.0
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

        return parent::store($updateNulls);
    }
}
