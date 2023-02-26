<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Downloadkey
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper as ComInstallerHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! update notification plugin
 *
 * @since  4.0.0
 */
class PlgQuickiconDownloadkey extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Returns an icon definition for an icon which looks for extensions updates
     * via AJAX and displays a notification when such updates are found.
     *
     * @param   string  $context  The calling context
     *
     * @return  array  A list of icon definition associative arrays, consisting of the
     *                 keys link, image, text and access.
     *
     * @since   4.0.0
     */
    public function onGetIcons($context)
    {
        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->app->getIdentity()->authorise('core.manage', 'com_installer')
        ) {
            return [];
        }

        $info = $this->getMissingDownloadKeyInfo();

        // No extensions need a download key. The icon is not rendered.
        if (!$info['supported']) {
            return [];
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
            $iconDefinition = array_merge($iconDefinition, [
                'link'  => 'index.php?option=com_installer&view=updatesites&filter[supported]=-1',
                'text'  => Text::plural('PLG_QUICKICON_DOWNLOADKEY_N_MISSING', $info['missing']),
                'class' => 'danger',
                ]);
        }

        return [
            $iconDefinition,
        ];
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
    public function getMissingDownloadKeyInfo(): array
    {
        $ret = [
            'supported' => 0,
            'missing'   => 0,
        ];

        if (!class_exists('Joomla\Component\Installer\Administrator\Helper\InstallerHelper')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_installer/Helper/InstallerHelper.php';
        }

        $supported        = ComInstallerHelper::getDownloadKeySupportedSites(true);
        $ret['supported'] = count($supported);

        if ($ret['supported'] === 0) {
            return $ret;
        }

        $missing        = ComInstallerHelper::getDownloadKeyExistsSites(false, true);
        $ret['missing'] = count($missing);

        return $ret;
    }
}
