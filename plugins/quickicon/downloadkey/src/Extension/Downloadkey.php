<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.downloadkey
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\Downloadkey\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper as ComInstallerHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! update notification plugin
 *
 * @since  4.0.0
 */
final class Downloadkey extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
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
     * @since   4.0.0
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

        $info = $this->getMissingDownloadKeyInfo();

        // No extensions need a download key. The icon is not rendered.
        if (!$info['supported']) {
            return;
        }

        $iconDefinition = [
            'link'  => 'index.php?option=com_installer&view=updatesites&filter[supported]=1',
            'image' => 'icon-key',
            'icon'  => '',
            'text'  => Text::_('PLG_QUICKICON_DOWNLOADKEY_OK'),
            'class' => 'success',
            'id'    => 'plg_quickicon_downloadkey',
            'group' => 'MOD_QUICKICON_MAINTENANCE',
        ];

        if ($info['missing'] !== 0) {
            $iconDefinition = array_merge(
                $iconDefinition,
                [
                    'link'  => 'index.php?option=com_installer&view=updatesites&filter[supported]=-1',
                    'text'  => Text::plural('PLG_QUICKICON_DOWNLOADKEY_N_MISSING', $info['missing']),
                    'class' => 'danger',
                ]
            );
        }

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            $iconDefinition,
        ];

        $event->setArgument('result', $result);
    }

    /**
     * Gets the information about update sites requiring but missing a download key.
     *
     * The return array has two keys:
     * - supported  Number of update sites supporting Download Key
     * - missing    Number of update sites missing a Download Key
     *
     * If 'supported' is zero you do not need to provide any download keys. All your extensions are free downloads.
     *
     * If 'supported' is non-zero and 'missing' is zero you have entered a download key for all paid extensions.
     *
     * If 'supported' is non-zero and 'missing' is also non-zero you need to enter one or more download keys.
     *
     * @return  array
     * @since   4.0.0
     */
    private function getMissingDownloadKeyInfo(): array
    {
        $ret = [
            'supported' => 0,
            'missing'   => 0,
        ];

        if (!class_exists('Joomla\Component\Installer\Administrator\Helper\InstallerHelper')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_installer/Helper/InstallerHelper.php';
        }

        $supported        = ComInstallerHelper::getDownloadKeySupportedSites(true);
        $ret['supported'] = \count($supported);

        if ($ret['supported'] === 0) {
            return $ret;
        }

        $missing        = ComInstallerHelper::getDownloadKeyExistsSites(false, true);
        $ret['missing'] = \count($missing);

        return $ret;
    }
}
