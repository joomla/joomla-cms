<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ViewLevel table class.
 *
 * @since  1.7.0
 */
class ViewLevel extends Table
{
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
        parent::__construct('#__viewlevels', 'id', $db, $dispatcher);
    }

    /**
     * Method to bind the data.
     *
     * @param   array  $array   The data to bind.
     * @param   mixed  $ignore  An array or space separated list of fields to ignore.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.7.0
     */
    public function bind($array, $ignore = '')
    {
        // Bind the rules as appropriate.
        if (isset($array['rules'])) {
            if (\is_array($array['rules'])) {
                $array['rules'] = json_encode($array['rules']);
            }
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to check the current record to save
     *
     * @return  boolean  True on success
     *
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

        // Validate the title.
        if ((trim($this->title)) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_VIEWLEVEL'));

            return false;
        }

        $id = (int) $this->id;

        // Check for a duplicate title.
        $db    = $this->_db;
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('title') . ')')
            ->from($db->quoteName('#__viewlevels'))
            ->where($db->quoteName('title') . ' = :title')
            ->where($db->quoteName('id') . ' != :id')
            ->bind(':title', $this->title)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        if ($db->loadResult() > 0) {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_USERLEVEL_NAME_EXISTS', $this->title));

            return false;
        }

        return true;
    }
}
