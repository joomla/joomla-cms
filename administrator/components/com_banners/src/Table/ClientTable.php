<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Table;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Client table
 *
 * @since  1.6
 */
class ClientTable extends Table implements VersionableTableInterface
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.5
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_banners.client';

        $this->setColumnAlias('published', 'state');

        parent::__construct('#__banner_clients', 'id', $db, $dispatcher);
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True if the object is ok
     *
     * @see     Table::check()
     * @since   4.0.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            // @todo: 6.0 - Update Error handling
            $this->setError($e->getMessage());

            return false;
        }

        // Check for valid name
        if (trim($this->name) === '') {
            // @todo: 6.0 - Update Error handling
            $this->setError(Text::_('COM_BANNERS_WARNING_PROVIDE_VALID_NAME'));

            return false;
        }

        // Check for valid contact
        if (trim($this->contact) === '') {
            // @todo: 6.0 - Update Error handling
            $this->setError(Text::_('COM_BANNERS_PROVIDE_VALID_CONTACT'));

            return false;
        }

        return true;
    }
}
