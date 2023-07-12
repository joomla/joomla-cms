<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.privacycheck
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\PrivacyCheck\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to check privacy requests older than 14 days
 *
 * @since  3.9.0
 */
final class PrivacyCheck extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'onGetIcons',
        ];
    }

    /**
     * Check privacy requests older than 14 days.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onGetIcons(QuickIconsEvent $event): void
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.admin', 'com_privacy')
            || !ComponentHelper::isEnabled('com_privacy')
        ) {
            return;
        }

        $token    = Session::getFormToken() . '=' . 1;
        $privacy  = 'index.php?option=com_privacy';

        $options  = [
            'plg_quickicon_privacycheck_url'      => Uri::base() . $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
            'plg_quickicon_privacycheck_ajax_url' => Uri::base() . $privacy . '&task=getNumberUrgentRequests&format=json&' . $token,
            'plg_quickicon_privacycheck_text'     => [
                "NOREQUEST"            => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_NOREQUEST'),
                "REQUESTFOUND"         => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND'),
                "ERROR"                => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_ERROR'),
                "REQUESTFOUND_MESSAGE" => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_MESSAGE'),
                "REQUESTFOUND_BUTTON"  => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_BUTTON'),
            ],
        ];

        $this->getApplication()->getDocument()->addScriptOptions('js-privacy-check', $options);

        $this->getApplication()->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_privacycheck', 'plg_quickicon_privacycheck/privacycheck.js', [], ['defer' => true], ['core']);

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            [
                'link'  => $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
                'image' => 'icon-users',
                'icon'  => '',
                'text'  => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_PRIVACYCHECK_CHECKING'),
                'id'    => 'plg_quickicon_privacycheck',
                'group' => 'MOD_QUICKICON_USERS',
            ],
        ];

        $event->setArgument('result', $result);
    }
}
