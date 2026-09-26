<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PrivacyDashboard\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  3.9.0
 */
class PrivacyDashboardHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Method to retrieve information about the site privacy requests
     *
     * @return  array  Array containing site privacy requests
     *
     * @since   5.4.0
     */
    public function getPrivacyRequests(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(
                [
                    'COUNT(*) AS count',
                    $db->quoteName('status'),
                    $db->quoteName('request_type'),
                ]
            )
            ->from($db->quoteName('#__privacy_requests'))
            ->group($db->quoteName('status'))
            ->group($db->quoteName('request_type'));

        $db->setQuery($query);

        try {
            return $db->loadObjectList();
        } catch (ExecutionFailureException) {
            return [];
        }
    }

    /**
     * Method to retrieve information about the site privacy requests
     *
     * @return  array  Array containing site privacy requests
     *
     * @since   3.9.0
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getPrivacyRequests
     *             Example: Factory::getApplication()->bootModule('mod_privacy_dashboard', 'administrator')
     *                          ->getHelper('PrivacyDashboardHelper')
     *                          ->getPrivacyRequests()
     */
    public static function getData()
    {
        return Factory::getApplication()->bootModule('mod_privacy_dashboard', 'administrator')
                                  ->getHelper('PrivacyDashboardHelper')
                                  ->getPrivacyRequests();
    }
}
