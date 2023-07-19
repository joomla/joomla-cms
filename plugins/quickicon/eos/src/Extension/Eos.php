<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.eos
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\Eos\Extension;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! end of support notification plugin
 *
 * @since 4.4.0
 */
final class Eos extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The EOS date for 4.4.
     *
     * @var    string
     * @since 4.4.0
     */
    private const EOS_DATE = '2025-10-17';

    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  4.4.0
     */
    protected $autoloadLanguage = false;

    /**
     * Holding the current valid message to be shown.
     *
     * @var    array
     * @since 4.4.0
     */
    private $currentMessage = [];

    /**
     * Are the messages initialized.
     *
     * @var    bool
     * @since 4.4.0
     */

    private $messagesInitialized = false;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since 4.4.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'getEndOfServiceNotification',
            'onAjaxEos'  => 'onAjaxEos',
        ];
    }

    /**
     * Check and show the the alert.
     *
     * This method is called when the Quick Icons module is constructing its set
     * of icons.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since 4.4.0
     *
     * @throws \Exception
     */
    public function getEndOfServiceNotification(QuickIconsEvent $event): void
    {
        $app = $this->getApplication();

        if (
            $event->getContext() !== $this->params->get('context', 'update_quickicon')
            || !$this->shouldDisplayMessage()
            || (!$this->messagesInitialized && $this->setMessage() == [])
            || !$app instanceof CMSWebApplicationInterface
        ) {
            return;
        }

        $this->loadLanguage();

        // Show this only when not snoozed
        if ($this->params->get('last_snoozed_id', 0) < $this->currentMessage['id']) {
            // Build the  message to be displayed in the cpanel
            $messageText = sprintf(
                $app->getLanguage()->_($this->currentMessage['messageText']),
                HTMLHelper::_('date', Eos::EOS_DATE, $app->getLanguage()->_('DATE_FORMAT_LC3')),
                $this->currentMessage['messageLink']
            );
            if ($this->currentMessage['snoozable']) {
                $messageText .= '<p><button class="btn btn-warning eosnotify-snooze-btn" type="button" >';
                $messageText .= $app->getLanguage()->_('PLG_QUICKICON_EOS_SNOOZE_BUTTON') . '</button></p>';
            }
            $app->enqueueMessage($messageText, $this->currentMessage['messageType']);
        }

        $app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_eos.script', 'plg_quickicon_eos/snooze.js', [], ['type' => 'module']);
    }

    /**
     * Save the plugin parameters.
     *
     * @return  bool
     *
     * @since 4.4.0
     */
    private function saveParams(): bool
    {
        $params = $this->params->toString('JSON');
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('quickicon'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('eos'))
            ->bind(':params', $params);

        return $db->setQuery($query)->execute();
    }

    /**
     * Determines if the message and quickicon should be displayed.
     *
     * @return  bool
     *
     * @since 4.4.0
     *
     * @throws \Exception
     */
    private function shouldDisplayMessage(): bool
    {
        // Show only on administration part
        return $this->getApplication()->isClient('administrator')
            // Only show for HTML requests
            && $this->getApplication()->getDocument()->getType() === 'html'
            // Don't show in modal
            && $this->getApplication()->getInput()->getCmd('tmpl', 'index') !== 'component'
            // Only show in cpanel
            && $this->getApplication()->getInput()->get('option') === 'com_cpanel';
    }

    /**
     * Return the texts to be displayed based on the time until we reach EOS.
     *
     * @param   int  $monthsUntilEOS  The months until we reach EOS
     * @param   int  $inverted        Have we surpassed the EOS date
     *
     * @return  array  An array with the message to be displayed or false
     *
     * @since 4.4.0
     */
    private function getMessageInfo(int $monthsUntilEOS, int $inverted): array
    {
        // The EOS date has passed - Support has ended
        if ($inverted === 1) {
            return [
                'id'          => 5,
                'messageText' => 'PLG_QUICKICON_EOS_MESSAGE_ERROR_SUPPORT_ENDED',
                'messageType' => 'error',
                'messageLink' => 'https://docs.joomla.org/Special:MyLanguage/Joomla_4.4.x_to_5.x_Planning_and_Upgrade_Step_by_Step',
                'snoozable'   => false,
            ];
        }

        // The security support is ending in 6 months
        if ($monthsUntilEOS < 6) {
            return [
                'id'          => 4,
                'messageText' => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SUPPORT_ENDING',
                'messageType' => 'warning',
                'messageLink' => 'https://docs.joomla.org/Special:MyLanguage/Joomla_4.4.x_to_5.x_Planning_and_Upgrade_Step_by_Step',
                'snoozable'   => true,
            ];
        }

        // We are in security only mode now, 12 month to go from now on
        if ($monthsUntilEOS < 12) {
            return [
                'id'          => 3,
                'messageText' => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SECURITY_ONLY',
                'messageType' => 'warning',
                'messageLink' => 'https://docs.joomla.org/Special:MyLanguage/Joomla_4.4.x_to_5.x_Planning_and_Upgrade_Step_by_Step',
                'snoozable'   => true,
            ];
        }

        // We still have 16 month to go, lets remind our users about the pre upgrade checker
        if ($monthsUntilEOS < 16) {
            return [
                'id'          => 2,
                'messageText' => 'PLG_QUICKICON_EOS_MESSAGE_INFO_02',
                'messageType' => 'info',
                'messageLink' => 'https://docs.joomla.org/Special:MyLanguage/Pre-Update_Check',
                'snoozable'   => true,
            ];
        }

        // Lets start our messages 2 month after the initial release, still 22 month to go
        if ($monthsUntilEOS < 22) {
            return [
                'id'          => 1,
                'messageText' => 'PLG_QUICKICON_EOS_MESSAGE_INFO_01',
                'messageType' => 'info',
                'messageLink' => 'https://joomla.org/5',
                'snoozable'   => true,
            ];
        }

        return [];
    }

    /**
     * Check if current user is allowed to send the data.
     *
     * @return  bool
     *
     * @since 4.4.0
     *
     * @throws \Exception
     */
    private function isAllowedUser(): bool
    {
        return $this->getApplication()->getIdentity()->authorise('core.login.admin');
    }

    /**
     * User hit the snooze button.
     *
     * @return  string
     *
     * @since 4.4.0
     *
     * @throws  Notallowed  If user is not allowed
     *
     * @throws \Exception
     */
    public function onAjaxEos(): string
    {
        // No messages yet so nothing to snooze
        if (!$this->messagesInitialized && $this->setMessage() == []) {
            return '';
        }

        if (!$this->isAllowedUser()) {
            throw new Notallowed($this->getApplication()->getLanguage()->_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }

        // Make sure only snoozable messages can be snoozed
        if ($this->currentMessage['snoozable']) {
            $this->params->set('last_snoozed_id', $this->currentMessage['id']);
            $this->saveParams();
        }

        return '';
    }

    /**
     * Calculates how many days and selects correct message.
     *
     * @return array
     *
     * @since  4.4.0
     */
    private function setMessage(): array
    {
        $diff                      = Factory::getDate()->diff(Factory::getDate(Eos::EOS_DATE));
        $message                   = $this->getMessageInfo(floor($diff->days / 30.417), $diff->invert);
        $this->currentMessage      = $message;
        $this->messagesInitialized = true;

        return $message;
    }
}
