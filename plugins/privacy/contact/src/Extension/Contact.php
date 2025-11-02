<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.contact
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Contact\Extension;

use Joomla\CMS\Event\Privacy\ExportRequestEvent;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla user contact data
 *
 * @since  3.9.0
 */
final class Contact extends PrivacyPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPrivacyExportRequest' => 'onPrivacyExportRequest',
        ];
    }

    /**
     * Processes an export request for Joomla core user contact data
     *
     * This event will collect data for the contact core tables:
     *
     * - Contact custom fields
     *
     * @param   ExportRequestEvent  $event  The request event
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyExportRequest(ExportRequestEvent $event): void
    {
        $request = $event->getRequest();
        $user    = $event->getUser();

        if (!$user && !$request->email) {
            return;
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

        $event->addResult($domains);
    }
}
