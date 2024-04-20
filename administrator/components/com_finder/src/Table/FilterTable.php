<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Filter table class for the Finder package.
 *
 * @since  2.5
 */
class FilterTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Ensure the params are json encoded in the bind method
     *
     * @var    array
     * @since  4.0.0
     */
    protected $_jsonEncode = ['params'];

    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   2.5
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__finder_filters', 'filter_id', $db, $dispatcher);

        $this->setColumnAlias('published', 'state');
    }

    /**
     * Method to perform sanity checks on the \Joomla\CMS\Table\Table instance properties to ensure
     * they are safe to store in the database.  Child classes should override this
     * method to make sure the data they are storing in the database is safe and
     * as expected before storage.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
     *
     * @since   2.5
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (trim($this->alias) === '') {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        $params = new Registry($this->params);

        $d1 = $params->get('d1', '');
        $d2 = $params->get('d2', '');

        // Check the end date is not earlier than the start date.
        if (!empty($d1) && !empty($d2) && $d2 < $d1) {
            // Swap the dates.
            $params->set('d1', $d2);
            $params->set('d2', $d1);
            $this->params = (string) $params;
        }

        return true;
    }

    /**
     * Method to store a row in the database from the \Joomla\CMS\Table\Table instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * \Joomla\CMS\Table\Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null. [optional]
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = $this->getCurrentUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->filter_id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified) {
                $this->modified = $this->created;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $this->created_by;
            }
        }

        if (\is_array($this->data)) {
            $this->map_count = \count($this->data);
            $this->data      = implode(',', $this->data);
        } else {
            $this->map_count = 0;
            $this->data      = implode(',', []);
        }

        // Verify that the alias is unique
        $table = new self($this->getDbo(), $this->getDispatcher());

        if ($table->load(['alias' => $this->alias]) && ($table->filter_id != $this->filter_id || $this->filter_id == 0)) {
            $this->setError(Text::_('COM_FINDER_FILTER_ERROR_UNIQUE_ALIAS'));

            return false;
        }

        return parent::store($updateNulls);
    }
}
