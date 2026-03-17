<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Table;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Message Table class
 *
 * @since  1.5
 */
class MessageTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.5
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__messages', 'message_id', $db, $dispatcher);

        $this->setColumnAlias('published', 'state');
    }

    /**
     * Validation and filtering.
     *
     * @return  boolean
     *
     * @since   1.5
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check the to and from users.
        $user = new User($this->user_id_from);

        if (empty($user->id)) {
            $this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_FROM_USER'));

            return false;
        }

        $user = new User($this->user_id_to);

        if (empty($user->id)) {
            $this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_TO_USER'));

            return false;
        }

        if (empty($this->subject)) {
            $this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_SUBJECT'));

            return false;
        }

        if (empty($this->message)) {
            $this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_MESSAGE'));

            return false;
        }

        return true;
    }
}
