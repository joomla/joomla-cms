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
use Joomla\Database\Exception\ExecutionFailureException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  3.9.0
 */
class PrivacyDashboardHelper
{
    /**
     * Method to retrieve information about the site privacy requests
     *
     * @return  array  Array containing site privacy requests
     *
     * @since   3.9.0
     */
    public static function getData()
    {
        $db    = Factory::getDbo();
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
        } catch (ExecutionFailureException $e) {
            return [];
        }
    }
}
