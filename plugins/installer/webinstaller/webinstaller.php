<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.webinstaller
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Rule\UrlRule;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Support for the "Install from Web" tab
 *
 * @since  3.2
 */
class PlgInstallerWebinstaller extends CMSPlugin
{
    /**
     * The URL for the remote server.
     *
     * @var    string
     * @since  4.0.0
     */
    public const REMOTE_URL = 'https://appscdn.joomla.org/webapps/';

    /**
     * The application object.
     *
     * @var    CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * The URL to install from
     *
     * @var    string|null
     * @since  4.0.0
     */
    private $installfrom = null;

    /**
     * Flag if the document is in a RTL direction
     *
     * @var    integer|null
     * @since  4.0.0
     */
    private $rtl = null;

    /**
     * Event listener for the `onInstallerAddInstallationTab` event.
     *
     * @return  array  Returns an array with the tab information
     *
     * @since   4.0.0
     */
    public function onInstallerAddInstallationTab()
    {
        // Load language files
        $this->loadLanguage();

        $installfrom = $this->getInstallFrom();
        $doc         = $this->app->getDocument();
        $lang        = $this->app->getLanguage();

        // Push language strings to the JavaScript store
        Text::script('PLG_INSTALLER_WEBINSTALLER_CANNOT_INSTALL_EXTENSION_IN_PLUGIN');
        Text::script('PLG_INSTALLER_WEBINSTALLER_REDIRECT_TO_EXTERNAL_SITE_TO_INSTALL');

        $doc->getWebAssetManager()
            ->registerAndUseStyle('plg_installer_webinstaller.client', 'plg_installer_webinstaller/client.min.css')
            ->registerAndUseScript(
                'plg_installer_webinstaller.client',
                'plg_installer_webinstaller/client.min.js',
                [],
                ['type' => 'module'],
                ['core']
            );

        $devLevel = Version::PATCH_VERSION;

        if (!empty(Version::EXTRA_VERSION)) {
            $devLevel .= '-' . Version::EXTRA_VERSION;
        }

        $doc->addScriptOptions(
            'plg_installer_webinstaller',
            [
                'base_url'        => addslashes(self::REMOTE_URL),
                'installat_url'   => base64_encode(Uri::current() . '?option=com_installer&view=install'),
                'installfrom_url' => addslashes($installfrom),
                'product'         => base64_encode(Version::PRODUCT),
                'release'         => base64_encode(Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION),
                'dev_level'       => base64_encode($devLevel),
                'installfromon'   => $installfrom ? 1 : 0,
                'language'        => base64_encode($lang->getTag()),
                'installFrom'     => $installfrom != '' ? 4 : 5,
            ]
        );

        $tab = [
            'name'  => 'web',
            'label' => Text::_('PLG_INSTALLER_WEBINSTALLER_TAB_LABEL'),
        ];

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'webinstaller');
        $tab['content'] = ob_get_clean();
        $tab['content'] = '<legend>' . $tab['label'] . '</legend>' . $tab['content'];

        return $tab;
    }

    /**
     * Internal check to determine if the output is in a RTL direction
     *
     * @return  integer
     *
     * @since   3.2
     */
    private function isRTL()
    {
        if ($this->rtl === null) {
            $this->rtl = strtolower($this->app->getDocument()->getDirection()) === 'rtl' ? 1 : 0;
        }

        return $this->rtl;
    }

    /**
     * Get the install from URL
     *
     * @return  string
     *
     * @since   3.2
     */
    private function getInstallFrom()
    {
        if ($this->installfrom === null) {
            $installfrom = base64_decode($this->app->input->getBase64('installfrom', ''));

            $field = new SimpleXMLElement('<field></field>');

            if ((new UrlRule())->test($field, $installfrom) && preg_match('/\.xml\s*$/', $installfrom)) {
                $update = new Update();
                $update->loadFromXml($installfrom);
                $package_url = trim($update->get('downloadurl', false)->_data);

                if ($package_url) {
                    $installfrom = $package_url;
                }
            }

            $this->installfrom = $installfrom;
        }

        return $this->installfrom;
    }
}
