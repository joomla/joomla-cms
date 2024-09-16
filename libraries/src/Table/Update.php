<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Update table
 * Stores updates temporarily
 *
 * @since  1.7.0
 */
class Update extends Table
{
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
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__updates', 'update_id', $db, $dispatcher);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True if the object is ok
     *
     * @see     Table::check()
     * @since   1.7.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for valid name
        if (trim($this->name) == '' || trim($this->element) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

            return false;
        }

        if (!$this->update_id && !$this->data) {
            $this->data = '';
        }

        // While column is not nullable, make sure we have a value.
        if ($this->description === null) {
            $this->description = '';
        }

        return true;
    }

    /**
     * Method to create and execute a SELECT WHERE query.
     *
     * @param   array  $options  Array of options
     *
     * @return  string  Results of query
     *
     * @since   1.7.0
     */
    public function find($options = [])
    {
        $where = [];

        foreach ($options as $col => $val) {
            $where[] = $col . ' = ' . $this->_db->quote($val);
        }

        $query = $this->_db->getQuery(true)
            ->select($this->_db->quoteName($this->_tbl_key))
            ->from($this->_db->quoteName($this->_tbl))
            ->where(implode(' AND ', $where));
        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }
}
