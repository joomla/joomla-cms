<?php

/**
 * @package       Joomla.Plugin
 * @subpackage    Quickicon.Eos
 *
 * @copyright (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @phpcs         :disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! end of support notification plugin
 *
 * @since __DEPLOY_VERSION__
 */
class PlgQuickiconEos extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var   bool
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var   CMSApplication
     * @since __DEPLOY_VERSION__
     */
    protected $app;
    /**
     * The Date of End of Service
     *
     * @var Date
     *
     * @since __DEPLOY_VERSION__
     */
    protected Date $endOfServiceDate;
    /**
     * The Major version number of the next release
     *
     * @var int
     *
     * @since __DEPLOY_VERSION__
     */
    protected int $nextJoomlaMajor = 0;
    /**
     * The Minor version number of the next release
     *
     * @var int
     *
     * @since __DEPLOY_VERSION__
     */
    protected int $nextJoomlaMinor = 0;
    /**
     * The Patch version number of the next release
     *
     * @var int
     *
     * @since __DEPLOY_VERSION__
     */
    protected int $nextJoomlaPatch = 0;
    /**
     * Holding the current valid message to be shown
     *
     * @var   array
     * @since __DEPLOY_VERSION__
     */
    private array $currentMessage = [];

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups   The cache groups to clean
     * @param   array  $cacheClients  The cache clients (site, admin) to clean
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
    {
        foreach ($clearGroups as $group) {
            foreach ($cacheClients as $client_id) {
                try {
                    $options = [
                        'defaultgroup' => $group,
                        'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' : $this->app->get('cache_path', JPATH_SITE . '/cache')
                    ];

                    $cachecontroller = new CacheController($options);
                    $cache           = $cachecontroller->cache;
                    $cache->clean();
                } catch (Exception $e) {
                    // Ignore it
                }
            }
        }
    }

    /**
     * Return the texts to be displayed based on the time until we reach EOS
     *
     * @param   int  $monthsUntilEOS  The months until we reach EOS
     * @param   int  $inverted        Have we surpassed the EOS date
     *
     * @return array|bool  An array with the message to be displayed or false
     *
     * @since __DEPLOY_VERSION__
     */
    private function getMessageInfo(int $monthsUntilEOS, int $inverted): bool|array
    {
        // The EOS date has passed - Support has ended
        // messageLink needs updating to a generic migration link
        if ($inverted === 1) {
            return [
                'id'            => 5,
                'messageText'   => 'PLG_QUICKICON_EOS_MESSAGE_ERROR_SUPPORT_ENDED',
                'quickiconText' => 'PLG_QUICKICON_EOS_MESSAGE_ERROR_SUPPORT_ENDED_SHORT',
                'messageType'   => 'error',
                'image'         => 'minus-circle',
                'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
                'groupText'     => 'PLG_QUICKICON_EOS_GROUPNAME_EOS',
                'snoozable'     => false,
            ];
        }

        // The security support is ending in 6 months
        if ($monthsUntilEOS < 6) {
            return [
                'id'            => 4,
                'messageText'   => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SUPPORT_ENDING',
                'quickiconText' => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SUPPORT_ENDING_SHORT',
                'messageType'   => 'warning',
                'image'         => 'warning-circle',
                'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
                'groupText'     => 'PLG_QUICKICON_EOS_GROUPNAME_WARNING',
                'snoozable'     => true,
            ];
        }

        // We are in security only mode now, 12 month to go from now on
        if ($monthsUntilEOS < 12) {
            return [
                'id'            => 3,
                'messageText'   => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SECURITY_ONLY',
                'quickiconText' => 'PLG_QUICKICON_EOS_MESSAGE_WARNING_SECURITY_ONLY_SHORT',
                'messageType'   => 'warning',
                'image'         => 'warning-circle',
                'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
                'groupText'     => 'PLG_QUICKICON_EOS_GROUPNAME_WARNING',
                'snoozable'     => true,
            ];
        }

        // We still have 16 month to go, lets remind our users about the pre upgrade checker
        if ($monthsUntilEOS < 16) {
            return [
                'id'            => 2,
                'messageText'   => 'PLG_QUICKICON_EOS_MESSAGE_INFO_02',
                'quickiconText' => 'PLG_QUICKICON_EOS_MESSAGE_INFO_02_SHORT',
                'messageType'   => 'info',
                'image'         => 'info-circle',
                'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Pre-Update_Check',
                'groupText'     => 'PLG_QUICKICON_EOS_GROUPNAME_INFO',
                'snoozable'     => true,
            ];
        }

        // Lets start our messages 2 month after the initial release, still 22 month to go
        if ($monthsUntilEOS < 22) {
            return [
                'id'            => 1,
                'messageText'   => 'PLG_QUICKICON_EOS_MESSAGE_INFO_01',
                'quickiconText' => 'PLG_QUICKICON_EOS_MESSAGE_INFO_01_SHORT',
                'messageType'   => 'info',
                'image'         => 'info-circle',
                'messageLink'   => 'https://www.joomla.org/4/#features',
                'groupText'     => 'PLG_QUICKICON_EOS_GROUPNAME_INFO',
                'snoozable'     => true,
            ];
        }

        return false;
    }

    /**
     * Check valid AJAX request
     *
     * @return bool
     *
     * @since __DEPLOY_VERSION__
     */
    private function isAjaxRequest(): bool
    {
        return strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }

    /**
     * Check if current user is allowed to send the data
     *
     * @return bool
     *
     * @since __DEPLOY_VERSION__
     */
    private function isAllowedUser(): bool
    {
        return Factory::getUser()->authorise('core.login.admin');
    }

    /**
     * User hit the snooze button
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     *
     * @throws JAccessExceptionNotallowed  If user is not allowed.
     */
    public function onAjaxSnoozeEOS()
    {
        // No messages yet so nothing to snooze
        if (!$this->currentMessage) {
            return;
        }

        if (!$this->isAllowedUser() || !$this->isAjaxRequest()) {
            throw new JAccessExceptionNotallowed(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }

        // Make sure only snoozable messages can be snoozed
        if ($this->currentMessage['snoozable']) {
            $this->params->set('last_snoozed_id', $this->currentMessage['id']);

            $this->saveParams();
        }
    }

    /**
     * Check and show the the alert and quickicon message
     *
     * @param   string  $context  The calling context
     *
     * @return array  A list of icon definition associative arrays, consisting of the
     *                 keys link, image, text and access.
     *
     * @since __DEPLOY_VERSION__
     */
    public function onGetIcons(string $context): array
    {
        if (!$this->shouldDisplayMessage()) {
            return [];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)->select($db->quoteName(['a.endofsupportdate', 'a.major', 'a.minor', 'a.patchversion']))->from($db->quoteName('#__eos', 'a'));
        $db->setQuery($query);
        $res = null;
        try {
            $res = $db->loadRow();
        } catch (RuntimeException $e) {
            return [];
        }
        $message = [];
        if (!is_null($res)) {
            $this->endOfServiceDate = new Date($res[0]);
            $this->nextJoomlaMajor  = $res[1];
            $this->nextJoomlaMinor  = $res[2];
            $this->nextJoomlaPatch  = $res[3];

            $diff           = Factory::getDate()->diff(Factory::getDate($this->endOfServiceDate));
            $monthsUntilEOS = floor($diff->days / 30.417);

            $message = $this->getMessageInfo($monthsUntilEOS, $diff->invert);
        }

        $this->currentMessage = $message;

        // No messages yet
        if (!$this->currentMessage) {
            return [];
        }

        // Show this only when not snoozed
        if ($this->params->get('last_snoozed_id', 0) < $this->currentMessage['id']) {
            // Load the snooze scripts.
            HTMLHelper::_('jquery.framework');

            try {
                $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
                $wa->getRegistry()->addExtensionRegistryFile('plg_quickicon_eos');
                $wa->useScript('plg_quickicon_eos.snooze');
            } catch (Exception $e) {
                echo $e->getMessage();
                exit();
            }


            // Build the  message to be displayed in the cpanel
            $messageText = Text::sprintf(
                $this->currentMessage['messageText'],
                HTMLHelper::_('date', $this->endOfServiceDate, Text::_('DATE_FORMAT_LC3')),
                $this->currentMessage['messageLink']
            );

            if ($this->currentMessage['snoozable']) {
                $messageText .= '<p><button class="btn btn-warning eosnotify-snooze-btn" type="button">' . Text::_('PLG_QUICKICON_EOS_SNOOZE_BUTTON') . '</button></p>';
            }

            $this->app->enqueueMessage(
                $messageText,
                $this->currentMessage['messageType']
            );
        }

        // The message as quickicon
        $messageTextQuickIcon = Text::sprintf(
            $this->currentMessage['quickiconText'],
            HTMLHelper::_(
                'date',
                $this->endOfServiceDate,
                Text::_('DATE_FORMAT_LC3')
            )
        );

        // The message as quickicon
        return [
            [
                'link'   => $this->currentMessage['messageLink'],
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
                'image'  => $this->currentMessage['image'],
                'text'   => $messageTextQuickIcon,
                'id'     => 'plg_quickicon_eos',
                'group'  => $this->currentMessage['groupText'],
            ]
        ];
    }

    /**
     * Save the plugin parameters
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function saveParams(): void
    {
        $query = $this->db->getQuery(true)->update($this->db->quoteName('#__extensions'))->set($this->db->quoteName('params') . ' = ' . $this->db->quote($this->params->toString('JSON')))->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('quickicon'))->where($this->db->quoteName('element') . ' = ' . $this->db->quote('eos'));

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $this->db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return;
        }

        try {
            // Update the plugin parameters
            $result = $this->db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins'], [0, 1]);
        } catch (Exception $exc) {
            // If we failed to execute
            $this->db->unlockTables();

            $result = false;
        }

        try {
            // Unlock the tables after writing
            $this->db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }
    }

    /**
     * Determines if the message and quickicon should be displayed
     *
     * @return bool
     *
     * @since __DEPLOY_VERSION__
     */
    private function shouldDisplayMessage(): bool
    {
        // Only on admin app
        if (!$this->app->isClient('administrator')) {
            return false;
        }

        // Only if authenticated
        if (Factory::getUser()->guest) {
            return false;
        }

        // Only on HTML documents
        if ($this->app->getDocument()->getType() !== 'html') {
            return false;
        }

        // Only on full page requests
        if ($this->app->input->getCmd('tmpl', 'index') === 'component') {
            return false;
        }

        // Only to com_cpanel
        if ($this->app->input->get('option') !== 'com_cpanel') {
            return false;
        }

        return true;
    }
}
