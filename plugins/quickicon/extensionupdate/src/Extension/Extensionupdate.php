<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.extensionupdate
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\Extensionupdate\Extension;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! update notification plugin
 *
 * @since  2.5
 */
final class Extensionupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
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
     * Returns an icon definition for an icon which looks for extensions updates
     * via AJAX and displays a notification when such updates are found.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onGetIcons(QuickIconsEvent $event): void
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_installer')
        ) {
            return;
        }

        $token    = Session::getFormToken() . '=1';
        $options  = [
            'url'     => Uri::base() . 'index.php?option=com_installer&view=update&task=update.find&' . $token,
            'ajaxUrl' => Uri::base() . 'index.php?option=com_installer&view=update&task=update.ajax&' . $token
                . '&cache_timeout=3600&eid=0&skip=' . ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id,
        ];

        $this->getApplication()->getDocument()->addScriptOptions('js-extensions-update', $options);

        Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPTODATE');
        Text::script('PLG_QUICKICON_EXTENSIONUPDATE_UPDATEFOUND');
        Text::script('PLG_QUICKICON_EXTENSIONUPDATE_ERROR');
        Text::script('MESSAGE');
        Text::script('ERROR');
        Text::script('INFO');
        Text::script('WARNING');

        $this->getApplication()->getDocument()->getWebAssetManager()
            ->registerAndUseScript(
                'plg_quickicon_extensionupdate',
                'plg_quickicon_extensionupdate/extensionupdatecheck.min.js',
                [],
                ['defer' => true],
                ['core']
            );

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            [
                'link'  => 'index.php?option=com_installer&view=update&task=update.find&' . $token,
                'image' => 'icon-star',
                'icon'  => '',
                'text'  => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_EXTENSIONUPDATE_CHECKING'),
                'id'    => 'plg_quickicon_extensionupdate',
                'group' => 'MOD_QUICKICON_MAINTENANCE',
            ],
        ];

        $event->setArgument('result', $result);
    }
}
