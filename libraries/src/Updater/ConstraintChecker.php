<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Version;

/**
 * ConstrainChecker Class
 *
 * @since  __DEPLOY_VERSION__
 */
class ConstraintChecker
{
    /**
     * Checks whether the passed constraints are matched
     *
     * @param array $candidate The provided constraints to be checked
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function check(array $candidate)
    {
        if (!isset($candidate['targetplatform'])) {
            // targetplatform is required
            return false;
        }

        // Check targetplatform
        if (!$this->checkTargetplatform($candidate['targetplatform'])) {
            return false;
        }

        // Check php_minimum, assume true when not set
        if (
            isset($candidate['php_minimum'])
            && !$this->checkPhpMinimum($candidate['php_minimum'])
        ) {
            return false;
        }

        // Check supported databases, assume true when not set
        if (
            isset($candidate['supported_databases'])
            && !$this->checkSupportedDatabases($candidate['supported_databases'])
        ) {
            return false;
        }

        // Check stability, assume true when not set
        if (
            isset($candidate['stability'])
            && !$this->checkStability($candidate['stability'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check the targetPlatform
     *
     * @param array $targetPlatform
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkTargetplatform(array $targetPlatform)
    {
        // Lower case and remove the exclamation mark
        $product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));

        // Check that the product matches and that the version matches (optionally a regexp)
        if (
            $product === $targetPlatform["name"]
            && preg_match('/^' . $targetPlatform["version"] . '/', JVERSION)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check the minimum PHP version
     *
     * @param string $phpMinimum The minimum php version to check
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkPhpMinimum(string $phpMinimum)
    {
        // Check if PHP version supported via <php_minimum> tag
        return version_compare(PHP_VERSION, $phpMinimum, '>=');
    }

    /**
     * Check the supported databases and versions
     *
     * @param array $supportedDatabases array of supported databases and versions
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkSupportedDatabases(array $supportedDatabases)
    {
        $db        = Factory::getDbo();
        $dbType    = strtolower($db->getServerType());
        $dbVersion = $db->getVersion();

        // MySQL and MariaDB use the same database driver but not the same version numbers
        if ($dbType === 'mysql') {
            // Check whether we have a MariaDB version string and extract the proper version from it
            if (stripos($dbVersion, 'mariadb') !== false) {
                // MariaDB: Strip off any leading '5.5.5-', if present
                $dbVersion = preg_replace('/^5\.5\.5-/', '', $dbVersion);
                $dbType    = 'mariadb';
            }
        }

        // Do we have an entry for the database?
        if (!empty($supportedDatabases["$dbType"])) {
            $minimumVersion = $supportedDatabases["$dbType"];

            return version_compare($dbVersion, $minimumVersion, '>=');
        }

        return false;
    }

    /**
     * Check the stability
     *
     * @param string $stability Stability to check
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function checkStability(string $stability)
    {
        $minimumStability = ComponentHelper::getParams('com_installer')->get('minimum_stability', Updater::STABILITY_STABLE);

        $stabilityInt = $this->stabilityToInteger($stability);

        if (($stabilityInt < $minimumStability)) {
            return false;
        }

        return true;
    }

    /**
     * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
     * dev, alpha, beta, rc, stable) it is ignored.
     *
     * @param string $tag The tag string, e.g. dev, alpha, beta, rc, stable
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function stabilityToInteger($tag)
    {
        $constant = '\\Joomla\\CMS\\Updater\\Updater::STABILITY_' . strtoupper($tag);

        if (\defined($constant)) {
            return \constant($constant);
        }

        return Updater::STABILITY_STABLE;
    }
}
