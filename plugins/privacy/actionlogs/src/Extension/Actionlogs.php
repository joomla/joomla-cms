<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Privacy\Actionlogs\Extension;

use Joomla\CMS\Event\Privacy\ExportRequestEvent;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy plugin managing Joomla actionlogs data
 *
 * @since  3.9.0
 */
final class Actionlogs extends PrivacyPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPrivacyExportRequest' => 'onPrivacyExportRequest',
        ];
    }

    /**
     * Processes an export request for Joomla core actionlog data
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

        // RequestTable $request, User $user = null
        if (!$user) {
            return;
        }

        $domain = $this->createDomain('user_action_logs', 'joomla_user_action_logs_data');
        $db     = $this->getDatabase();
        $userId = (int) $user->id;

        $query = $db->createQuery()
            ->select(['a.*', $db->quoteName('u.name')])
            ->from($db->quoteName('#__action_logs', 'a'))
            ->join('INNER', $db->quoteName('#__users', 'u'), $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('a.user_id') . ' = :id')
            ->bind(':id', $userId, ParameterType::INTEGER);

        $db->setQuery($query);

        $data = $db->loadObjectList();

        if (!\count($data)) {
            return;
        }

        $data    = ActionlogsHelper::getCsvData($data);
        $isFirst = true;

        foreach ($data as $item) {
            if ($isFirst) {
                $isFirst = false;

                continue;
            }

            $domain->addItem($this->createItemFromArray($item));
        }

        $event->addResult([$domain]);
    }
}
