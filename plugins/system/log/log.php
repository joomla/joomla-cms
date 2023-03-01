<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! System Logging Plugin.
 *
 * @since  1.5
 */
class PlgSystemLog extends CMSPlugin
{
    /**
     * Called if user fails to be logged in.
     *
     * @param   array  $response  Array of response data.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserLoginFailure($response)
    {
        $errorlog = [];

        switch ($response['status']) {
            case Authentication::STATUS_SUCCESS:
                $errorlog['status']  = $response['type'] . ' CANCELED: ';
                $errorlog['comment'] = $response['error_message'];
                break;

            case Authentication::STATUS_FAILURE:
                $errorlog['status']  = $response['type'] . ' FAILURE: ';

                if ($this->params->get('log_username', 0)) {
                    $errorlog['comment'] = $response['error_message'] . ' ("' . $response['username'] . '")';
                } else {
                    $errorlog['comment'] = $response['error_message'];
                }
                break;

            default:
                $errorlog['status']  = $response['type'] . ' UNKNOWN ERROR: ';
                $errorlog['comment'] = $response['error_message'];
                break;
        }

        Log::addLogger([], Log::INFO);

        try {
            Log::add($errorlog['comment'], Log::INFO, $errorlog['status']);
        } catch (Exception $e) {
            // If the log file is unwriteable during login then we should not go to the error page
            return;
        }
    }
}
