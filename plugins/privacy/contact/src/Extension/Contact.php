<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.contact
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Contact\Extension;

use Joomla\CMS\User\User;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla user contact data
 *
 * @since  3.9.0
 */
final class Contact extends PrivacyPlugin
{
    /**
     * Processes an export request for Joomla core user contact data
     *
     * This event will collect data for the contact core tables:
     *
     * - Contact custom fields
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
        if (!$user && !$request->email) {
            return [];
        }

        $domains   = [];
        $domain    = $this->createDomain('user_contact', 'joomla_user_contact_data');
        $domains[] = $domain;
        $db        = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__contact_details'))
            ->order($db->quoteName('ordering') . ' ASC');

        if ($user) {
            $query->where($db->quoteName('user_id') . ' = :id')
                ->bind(':id', $user->id, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('email_to') . ' = :email')
                ->bind(':email', $request->email);
        }

        $items = $db->setQuery($query)->loadObjectList();

        foreach ($items as $item) {
            $domain->addItem($this->createItemFromArray((array) $item));
        }

        $domains[] = $this->createCustomFieldsDomain('com_contact.contact', $items);

        return $domains;
    }
}
