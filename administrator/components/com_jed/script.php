<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Script file of JED Component
 *
 * @since  4.0.0
 */
class Com_JedInstallerScript
{
    /**
     * Minimum Joomla version to check
     *
     * @var    string
     * @since  4.0.0
     */

    private string $minimumJoomlaVersion = '4.0';


    /**
     * Minimum PHP version to check
     *
     * @var    string
     * @since  4.0.0
     */

    private string $minimumPHPVersion = JOOMLA_MINIMUM_PHP;


    /**
     * Method to install the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  4.0.0
     */

    public function install(Joomla\CMS\Installer\InstallerAdapter $parent): bool
    {
        echo Text::_('COM_JED_INSTALLERSCRIPT_INSTALL');

        return true;
    }

    /**
     * Function called after extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  4.0.0
     *

     */

    public function postflight(
        string $type,
        Joomla\CMS\Installer\InstallerAdapter $parent
    ): bool {

        echo Text::_('COM_JED_INSTALLERSCRIPT_POSTFLIGHT');


        return true;
    }

    /**
     * Function called before extension installation/update/removal procedure commences
     *
     * @param   string            $type    The type of change (install, update or discover_install, not uninstall)
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  4.0.0
     *
     * @throws Exception
     */

    public function preflight(
        string $type,
        Joomla\CMS\Installer\InstallerAdapter $parent
    ): bool {

        if ($type !== 'uninstall') {
            // Check for the minimum PHP version before continuing

            if (!empty($this->minimumPHPVersion) && version_compare(PHP_VERSION, $this->minimumPHPVersion, '<')) {
                Log::add(
                    Text::sprintf('JLIB_INSTALLER_MINIMUM_PHP', $this->minimumPHPVersion),
                    Log::WARNING,
                    'jerror'
                );


                return false;
            }


            // Check for the minimum Joomla version before continuing

            if (!empty($this->minimumJoomlaVersion) && version_compare(JVERSION, $this->minimumJoomlaVersion, '<')) {
                Log::add(
                    Text::sprintf('JLIB_INSTALLER_MINIMUM_JOOMLA', $this->minimumJoomlaVersion),
                    Log::WARNING,
                    'jerror'
                );


                return false;
            }
        }


        echo Text::_('COM_JED_INSTALLERSCRIPT_PREFLIGHT');


        return true;
    }

    /**
     * Method to uninstall the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  4.0.0
     */

    public function uninstall(
        Joomla\CMS\Installer\InstallerAdapter $parent
    ): bool {

        echo Text::_('COM_JED_INSTALLERSCRIPT_UNINSTALL');


        return true;
    }

    /**
     * Method to update the extension
     *
     * @param   InstallerAdapter  $parent  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since  4.0.0
     *

     */

    public function update(
        Joomla\CMS\Installer\InstallerAdapter $parent
    ): bool {

        echo Text::_('COM_JED_INSTALLERSCRIPT_UPDATE');


        return true;
    }
}
