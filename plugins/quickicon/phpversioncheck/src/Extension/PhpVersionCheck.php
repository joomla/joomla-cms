<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.phpversioncheck
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\PhpVersionCheck\Extension;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to check the PHP version and display a warning about its support status
 *
 * @since  3.7.0
 */
final class PhpVersionCheck extends CMSPlugin implements SubscriberInterface
{
    /**
     * Constant representing the active PHP version being fully supported
     *
     * @var    integer
     * @since  3.7.0
     */
    public const PHP_SUPPORTED = 0;

    /**
     * Constant representing the active PHP version receiving security support only
     *
     * @var    integer
     * @since  3.7.0
     */
    public const PHP_SECURITY_ONLY = 1;

    /**
     * Constant representing the active PHP version being unsupported
     *
     * @var    integer
     * @since  3.7.0
     */
    public const PHP_UNSUPPORTED = 2;

    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.7.0
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
     * Check the PHP version after the admin component has been dispatched.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onGetIcons(QuickIconsEvent $event): void
    {
        if (!$this->shouldDisplayMessage()) {
            return;
        }

        $supportStatus = $this->getPhpSupport();

        if ($supportStatus['status'] !== self::PHP_SUPPORTED) {
            // Enqueue the notification message; set a warning if receiving security support or "error" if unsupported
            switch ($supportStatus['status']) {
                case self::PHP_SECURITY_ONLY:
                    $this->getApplication()->enqueueMessage($supportStatus['message'], 'warning');

                    break;

                case self::PHP_UNSUPPORTED:
                    $this->getApplication()->enqueueMessage($supportStatus['message'], 'danger');

                    break;
            }
        }
    }

    /**
     * Gets PHP support status.
     *
     * @return  array  Array of PHP support data
     *
     * @since   3.7.0
     * @note    The dates used in this method should correspond to the dates given on PHP.net
     * @link    https://www.php.net/supported-versions.php
     * @link    https://www.php.net/eol.php
     */
    private function getPhpSupport()
    {
        $phpSupportData = [
            '7.2' => [
                'security' => '2019-11-30',
                'eos'      => '2020-11-30',
            ],
            '7.3' => [
                'security' => '2020-12-06',
                'eos'      => '2021-12-06',
            ],
            '7.4' => [
                'security' => '2021-11-28',
                'eos'      => '2022-11-28',
            ],
            '8.0' => [
                'security' => '2022-11-26',
                'eos'      => '2023-11-26',
            ],
            '8.1' => [
                'security' => '2023-11-25',
                'eos'      => '2025-12-31',
            ],
            '8.2' => [
                'security' => '2024-12-31',
                'eos'      => '2026-12-31',
            ],
            '8.3' => [
                'security' => '2025-12-31',
                'eos'      => '2027-12-31',
            ],
            '8.4' => [
                'security' => '2026-12-31',
                'eos'      => '2028-12-31',
            ],
        ];

        // Fill our return array with default values
        $supportStatus = [
            'status'  => self::PHP_SUPPORTED,
            'message' => null,
        ];

        // Check the PHP version's support status using the minor version
        $activePhpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        // Handle non standard strings like PHP 7.2.34-8+ubuntu18.04.1+deb.sury.org+1
        $phpVersion = preg_split('/-/', PHP_VERSION)[0];

        // Do we have the PHP version's data?
        if (isset($phpSupportData[$activePhpVersion])) {
            // First check if the version has reached end of support
            $today           = new Date();
            $phpEndOfSupport = new Date($phpSupportData[$activePhpVersion]['eos']);

            if ($phpNotSupported = $today > $phpEndOfSupport) {
                /*
                 * Find the oldest PHP version still supported that is newer than the current version,
                 * this is our recommendation for users on unsupported platforms
                 */
                foreach ($phpSupportData as $version => $versionData) {
                    $versionEndOfSupport = new Date($versionData['eos']);

                    if (version_compare($version, $activePhpVersion, 'ge') && ($today < $versionEndOfSupport)) {
                        $supportStatus['status']  = self::PHP_UNSUPPORTED;
                        $supportStatus['message'] = Text::sprintf(
                            'PLG_QUICKICON_PHPVERSIONCHECK_UNSUPPORTED',
                            $phpVersion,
                            $version,
                            $versionEndOfSupport->format(Text::_('DATE_FORMAT_LC4'))
                        );

                        return $supportStatus;
                    }
                }

                // PHP version is not supported and we don't know of any supported versions.
                $supportStatus['status']  = self::PHP_UNSUPPORTED;
                $supportStatus['message'] = Text::sprintf(
                    'PLG_QUICKICON_PHPVERSIONCHECK_UNSUPPORTED_JOOMLA_OUTDATED',
                    $phpVersion
                );

                return $supportStatus;
            }

            // If the version is still supported, check if it has reached eol minus 3 month
            $securityWarningDate = clone $phpEndOfSupport;
            $securityWarningDate->sub(new \DateInterval('P3M'));

            if (!$phpNotSupported && $today > $securityWarningDate) {
                $supportStatus['status']  = self::PHP_SECURITY_ONLY;
                $supportStatus['message'] = Text::sprintf(
                    'PLG_QUICKICON_PHPVERSIONCHECK_SECURITY_ONLY',
                    $phpVersion,
                    $phpEndOfSupport->format(Text::_('DATE_FORMAT_LC4'))
                );
            }
        }

        return $supportStatus;
    }

    /**
     * Determines if the message should be displayed
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    private function shouldDisplayMessage()
    {
        // Only on admin app
        if (!$this->getApplication()->isClient('administrator')) {
            return false;
        }

        // Only if authenticated
        if ($this->getApplication()->getIdentity()->guest) {
            return false;
        }

        // Only on HTML documents
        if ($this->getApplication()->getDocument()->getType() !== 'html') {
            return false;
        }

        // Only on full page requests
        if ($this->getApplication()->getInput()->getCmd('tmpl', 'index') === 'component') {
            return false;
        }

        // Only to com_cpanel
        if ($this->getApplication()->getInput()->get('option') !== 'com_cpanel') {
            return false;
        }

        return true;
    }
}
