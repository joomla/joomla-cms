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
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  1.7.0
 */
class Asset extends Nested
{
    /**
     * The primary key of the asset.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $id = null;

    /**
     * The unique name of the asset.
     *
     * @var    string
     * @since  1.7.0
     */
    public $name = null;

    /**
     * The human readable title of the asset.
     *
     * @var    string
     * @since  1.7.0
     */
    public $title = null;

    /**
     * The rules for the asset stored in a JSON string
     *
     * @var    string
     * @since  1.7.0
     */
    public $rules = null;

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
        parent::__construct('#__assets', 'id', $db, $dispatcher);
    }

    /**
     * Method to load an asset by its name.
     *
     * @param   string  $name  The name of the asset.
     *
     * @return  integer
     *
     * @since   1.7.0
     */
    public function loadByName($name)
    {
        return $this->load(['name' => $name]);
    }

    /**
     * Assert that the nested set data is valid.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
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

        $this->parent_id = (int) $this->parent_id;

        if (empty($this->rules)) {
            $this->rules = '{}';
        }

        // Nested does not allow parent_id = 0, override this.
        if ($this->parent_id > 0) {
            // Get the DatabaseQuery object
            $db    = $this->getDatabase();
            $query = $db->createQuery()
                ->select('1')
                ->from($db->quoteName($this->_tbl))
                ->where($db->quoteName('id') . ' = ' . $this->parent_id);

            $query->setLimit(1);

            if ($db->setQuery($query)->loadResult()) {
                return true;
            }

            $this->setError(Text::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'));

            return false;
        }

        return true;
    }
}
