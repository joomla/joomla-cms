<?php
/**
 * @package                 Joomla.Plugin
 * @subpackage              quickicon.eos
 *
 * @copyright           (C) 2023 Open Source Matters, Inc. <https:
 * //www.joomla.org>
 * @license                 GNU General Public License version 2 or later; see LICENSE.txt
 * @phpcs                   :disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

namespace Joomla\Plugin\Quickicon\Eos\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') || die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;

/**
 * Joomla! end of support notification plugin
 *
 * @since  4.0.0
 */
final class Eos extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * The EOS date for 4.x.
     *
     * @var    string
     * @since  4.0.0
     */
    const EOS_DATE = '2023-10-25';
    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;
    /**
     * Holding the current valid message to be shown
     *
     * @var    array|bool
     * @since  4.0.0
     */
    private array|bool $currentMessage = [];

    /**
     * The document.
     *
     * @var Document
     *
     * @since  4.0.0
     */
    private Document $document;

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function clearCacheGroups()
    {
        $clearGroups  = ['com_plugins'];
        $cacheClients = [0, 1];
        foreach ($clearGroups as $group) {
            foreach ($cacheClients as $client_id) {
                try {
                    $options         = ['defaultgroup' => $group, 'cachebase' => $client_id ? JPATH_ADMINISTRATOR . '/cache' : $this->getApplication()->get('cache_path', JPATH_SITE . '/cache')];
                    $cachecontroller = new CacheController($options);
                    $cache           = $cachecontroller->cache;
                    $cache->clean();
                } catch (Exception) {
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
     * @return  array|bool  An array with the message to be displayed or false
     *
     * @since   4.0.0
     */
    private function getMessageInfo(int $monthsUntilEOS, int $inverted): bool|array
    {
        // The EOS date has passed - Support has ended
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
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'onGetIcons',
        ];
    }

    /**
     * Check if current user is allowed to send the data
     *
     * @return  bool
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    private function isAllowedUser(): bool
    {
        return Factory::getApplication()->getIdentity()->authorise('core.login.admin');
    }

    /**
     * User hit the snooze button
     *
     * @return  string
     *
     * @since   4.0.0
     *
     * @throws  Notallowed  If user is not allowed.
     *
     * @throws Exception
     */
    public function onAjaxEos(): string
    {
        // No messages yet so nothing to snooze
        if (!$this->currentMessage) {
            return '';
        }
        if (!$this->isAllowedUser()) {
            throw new Notallowed(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }
        // Make sure only snoozable messages can be snoozed
        if ($this->currentMessage['snoozable']) {
            $this->params->set('last_snoozed_id', $this->currentMessage['id']);
            $saveok = $this->saveParams();
        }

        return '';
    }

    /**
     * Check and show the the alert and quickicon message
     *
     * @param   string  $context  The calling context
     *
     * @return  array  A list of icon definition associative arrays, consisting of the
     *                 keys link, image, text and access.
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function onGetIcons(string $context): array
    {
        if (!$this->shouldDisplayMessage()) {
            return [];
        }

        // No messages yet
        if (!$this->currentMessage) {
            return [];
        }

        // Show this only when not snoozed
        if ($this->params->get('last_snoozed_id', 0) < $this->currentMessage['id']) {
            // Build the  message to be displayed in the cpanel
            $messageText = Text::sprintf($this->currentMessage['messageText'], HTMLHelper::_('date', Eos::EOS_DATE, Text::_('DATE_FORMAT_LC3')), $this->currentMessage['messageLink']);
            if ($this->currentMessage['snoozable']) {
                $messageText .= '<p><button class="btn btn-warning eosnotify-snooze-btn" type="button" >' . Text::_('PLG_QUICKICON_EOS_SNOOZE_BUTTON') . '</button></p>';
            }
            $this->getApplication()->enqueueMessage($messageText, $this->currentMessage['messageType']);
        }
        try {
            $this->document->getWebAssetManager()->registerAndUseScript('boo', 'plg_quickicon_eos/snooze.js', [], ['type' => 'module']);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
        // The message as quickicon
        $messageTextQuickIcon = Text::sprintf($this->currentMessage['quickiconText'], HTMLHelper::_('date', Eos::EOS_DATE, Text::_('DATE_FORMAT_LC3')));

        // The message as quickicon
        return [['link' => $this->currentMessage['messageLink'], 'target' => '_blank', 'rel' => 'noopener noreferrer', 'image' => $this->currentMessage['image'], 'text' => $messageTextQuickIcon, 'id' => 'plg_quickicon_eos', 'group' => $this->currentMessage['groupText'],]];
    }

    /**
     * Save the plugin parameters
     *
     * @return  bool
     *
     * @since   4.0.0
     */
    private function saveParams(): bool
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)->update($db->quoteName('#__extensions'))->set($db->quoteName('params') . ' = ' . $db->quote($this->params->toString('JSON')))->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))->where($db->quoteName('folder') . ' = ' . $db->quote('quickicon'))->where($db->quoteName('element') . ' = ' . $db->quote('eos'));
        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception) {
            // If we can't lock the tables it's too risky to continue execution
            return false;
        }
        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();
            $this->clearCacheGroups();
        } catch (Exception) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }
        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        return $result;
    }

    /**
     * Determines if the message and quickicon should be displayed
     *
     * @return  bool
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    private function shouldDisplayMessage(): bool
    {
        if (
            !$this->getApplication()->isClient('administrator')
            || Factory::getApplication()->getIdentity()->guest
            || $this->document->getType() !== 'html'
            || $this->getApplication()->input->getCmd('tmpl', 'index') === 'component'
            || $this->getApplication()->input->get('option') !== 'com_cpanel'
        ) {
            return false;
        }

        return true;
    }

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject   The object to observe
     * @param   Document             $document  The document
     * @param   array                $config    An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'group', 'params', 'language'
     *                                          (this list is not meant to be comprehensive).
     *
     * @since   4.0.0
     */
    public function __construct($subject, Document $document, array $config = [])
    {
        parent::__construct($subject, $config);
        $this->document       = $document;
        $diff                 = Factory::getDate()->diff(Factory::getDate(Eos::EOS_DATE));
        $monthsUntilEOS       = floor($diff->days / 30.417);
        $message              = $this->getMessageInfo($monthsUntilEOS, $diff->invert);
        $this->currentMessage = $message;
    }
}
