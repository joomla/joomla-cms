<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension table
 *
 * @since  1.7.0
 */
class Extension extends Table
{
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
     * Custom data can be used by extension developers
     *
     * @var    string
     * @since  4.0.0
     */
    public $custom_data = '';

    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__extensions', 'extension_id', $db, $dispatcher);

        // Set the alias since the column is called enabled
        $this->setColumnAlias('published', 'enabled');
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
        if (trim($this->name) === '' || trim($this->element) === '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

            return false;
        }

        return true;
    }

    /**
     * Method to create and execute a SELECT WHERE query.
     *
     * @param   array  $options  Array of options
     *
     * @return  string  The database query result
     *
     * @since   1.7.0
     */
    public function find($options = [])
    {
        // Get the DatabaseQuery object
        $query = $this->_db->getQuery(true);

        foreach ($options as $col => $val) {
            $query->where($col . ' = ' . $this->_db->quote($val));
        }

        $query->select($this->_db->quoteName('extension_id'))
            ->from($this->_db->quoteName('#__extensions'));
        $this->_db->setQuery($query);

        return $this->_db->loadResult();
    }
}
