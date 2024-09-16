<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Downloadkey Table class.
 *
 * @since  4.0.0
 */
class UpdatesiteTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     *
     * @since  4.1.1
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   4.0.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->typeAlias = 'com_installer.downloadkey';

        parent::__construct('#__update_sites', 'update_site_id', $db, $dispatcher);
    }
}
