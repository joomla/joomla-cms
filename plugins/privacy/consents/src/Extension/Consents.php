<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.consents
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Consents\Extension;

use Joomla\CMS\Event\Privacy\ExportRequestEvent;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla user consent data
 *
 * @since  3.9.0
 */
final class Consents extends PrivacyPlugin implements SubscriberInterface
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
     * Processes an export request for Joomla core user consent data
     *
     * This event will collect data for the core `#__privacy_consents` table
     *
     * @param   ExportRequestEvent  $event  The request event
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyExportRequest(ExportRequestEvent $event): void
    {
        $user = $event->getUser();

        if (!$user) {
            return;
        }

        $domain = $this->createDomain('consents', 'joomla_consent_data');
        $db     = $this->getDatabase();

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__privacy_consents'))
            ->where($db->quoteName('user_id') . ' = :id')
            ->order($db->quoteName('created') . ' ASC')
            ->bind(':id', $user->id, ParameterType::INTEGER);

        $items = $db->setQuery($query)->loadAssocList();

        foreach ($items as $item) {
            $domain->addItem($this->createItemFromArray($item));
        }

        $event->addResult([$domain]);
    }
}
