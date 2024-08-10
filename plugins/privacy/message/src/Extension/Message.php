<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.message
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Message\Extension;

use Joomla\CMS\User\User;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla user messages
 *
 * @since  3.9.0
 */
final class Message extends PrivacyPlugin
{
    /**
     * Processes an export request for Joomla core user message
     *
     * This event will collect data for the message table
     *
     * @param   RequestTable  $request  The request record being processed
     * @param   User          $user     The user account associated with this request if available
     *
     * @return  \Joomla\Component\Privacy\Administrator\Export\Domain[]
     *
     * @since   3.9.0
     */
    public function onPrivacyExportRequest(RequestTable $request, User $user = null)
    {
        if (!$user) {
            return [];
        }

        $domain = $this->createDomain('user_messages', 'joomla_user_messages_data');
        $db     = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__messages'))
            ->where($db->quoteName('user_id_from') . ' = :useridfrom')
            ->extendWhere('OR', $db->quoteName('user_id_to') . ' = :useridto')
            ->order($db->quoteName('date_time') . ' ASC')
            ->bind([':useridfrom', ':useridto'], $user->id, ParameterType::INTEGER);

        $items = $db->setQuery($query)->loadAssocList();

        foreach ($items as $item) {
            $domain->addItem($this->createItemFromArray($item));
        }

        return [$domain];
    }
}
