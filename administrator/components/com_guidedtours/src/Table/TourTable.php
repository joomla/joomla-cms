<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tours table class.
 *
 * @since 4.3.0
 */
class TourTable extends Table implements CurrentUserInterface
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
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     * @since  4.3.0
     */
    protected $_jsonEncode = ['extensions'];

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   4.3.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__guidedtours', 'id', $db, $dispatcher);
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

        // set missing Uid
        if (empty($this->uid)) {
            $this->setTourUid();
        }

        // make sure the uid is unique
        $this->ensureUniqueUid();

        return parent::store($updateNulls);
    }


    /**
     * Method to set the uid when empty
     *
     * @return  string $uid  Contains the non-empty uid.
     *
     * @since   5.0.0
     */
    protected function setTourUid()
    {
        // Tour follows Joomla naming convention
        if (str_starts_with($this->title, 'COM_GUIDEDTOURS_TOUR_') && str_ends_with($this->title, '_TITLE')) {
            $uidTitle = 'joomla_' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $this->title);

            // Remove the last _TITLE part
            $pos = strrpos($uidTitle, "_TITLE");
            if ($pos !== false) {
                $uidTitle = substr($uidTitle, 0, $pos);
            }
        } elseif (preg_match('#COM_(\w+)_TOUR_#', $this->title) && str_ends_with($this->title, '_TITLE')) {
            // Tour follows component naming pattern
            $uidTitle = preg_replace('#COM_(\w+)_TOUR_#', '$1.', $this->title);

            // Remove the last _TITLE part
            $pos = strrpos($uidTitle, "_TITLE");
            if ($pos !== false) {
                $uidTitle = substr($uidTitle, 0, $pos);
            }
        } else {
            $uri      = Uri::getInstance();
            $host     = $uri->toString(['host']);
            $host     = ApplicationHelper::stringURLSafe($host, $this->language);
            $uidTitle = $host . ' ' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $this->title);
            // Remove the last _TITLE part
            if (str_ends_with($uidTitle, '_TITLE')) {
                $pos      = strrpos($uidTitle, '_TITLE');
                $uidTitle = substr($uidTitle, 0, $pos);
            }
        }
        // ApplicationHelper::stringURLSafe will replace a period (.) separator so we split the construction into multiple parts
        $uidTitleParts = explode('.', $uidTitle);
        array_walk($uidTitleParts, function (&$value, $key, $tourLanguage) {
            $value = ApplicationHelper::stringURLSafe($value, $tourLanguage);
        }, $this->language);
        $this->uid = implode('.', $uidTitleParts);

        $this->store();

        return $this->uid;
    }

    /**
     * Method to change the uid when not unique.
     *
     * @return  string $uid  Contains the modified uid.
     *
     * @since   5.0.0
     */
    protected function ensureUniqueUid()
    {
        $table  = new TourTable($this->_db);
        $unique = false;
        // Alter the title & uid
        while (!$unique) {
            // Attempt to load the row by uid.
            $uidItem = $table->load([ 'uid' => $this->uid ]);
            if ($uidItem && $table->id > 0 && $table->id != $this->id) {
                $this->uid = StringHelper::increment($this->uid, 'dash');
            } else {
                $unique = true;
            }
        }

        return $this->uid;
    }
}
