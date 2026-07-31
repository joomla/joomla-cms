<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.content
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Content\Extension;

use Joomla\CMS\Event\Privacy\ExportRequestEvent;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla user content data
 *
 * @since  3.9.0
 */
final class Content extends PrivacyPlugin implements SubscriberInterface
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
     * Processes an export request for Joomla core user content data
     *
     * This event will collect data for the content core table
     *
     * - Content custom fields
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

        $domains   = [];
        $domain    = $this->createDomain('user_content', 'joomla_user_content_data');
        $domains[] = $domain;
        $db        = $this->getDatabase();

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('created_by') . ' = ' . (int) $user->id)
            ->order($db->quoteName('ordering') . ' ASC');

        $items = $db->setQuery($query)->loadObjectList();

        foreach ($items as $item) {
            $domain->addItem($this->createItemFromArray((array) $item));
        }

        $domains[] = $this->createCustomFieldsDomain('com_content.article', $items);

        $event->addResult($domains);
    }
}
