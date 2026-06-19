<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Logged\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_logged
 *
 * @since  1.5
 */
class LoggedHelper
{
    /**
     * Get a list of logged users.
     *
     * @param   Registry           $params  The module parameters
     * @param   CMSApplication     $app     The application
     * @param   DatabaseInterface  $db      The database
     *
     * @return  mixed  An array of users, or false on error.
     *
     * @since   5.4.0
     *
     * @throws  \RuntimeException
     */
    public function getUsers(Registry $params, CMSApplication $app, DatabaseInterface $db): mixed
    {
        $user  = $app->getIdentity();
        $query = $db->createQuery()
            ->select('s.time, s.client_id, u.id, u.name, u.username')
            ->from('#__session AS s')
            ->join('RIGHT', '#__users AS u ON s.userid = u.id')
            ->where('s.guest = 0')
            ->order('s.time DESC')
            ->setLimit($params->get('count', 5), 0);

        $db->setQuery($query);

        try {
            $results = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            throw $e;
        }

        foreach ($results as $result) {
            $result->logoutLink = '';

            if ($user->authorise('core.manage', 'com_users')) {
                $result->editLink   = Route::_('index.php?option=com_users&task=user.edit&id=' . $result->id);
                $result->logoutLink = Route::_(
                    'index.php?option=com_login&task=logout&uid=' . $result->id . '&' . Session::getFormToken() . '=1'
                );
            }

            if ($params->get('name', 1) == 0) {
                $result->name = $result->username;
            }
        }

        return $results;
    }

    /**
     * Get the alternate title for the module
     *
     * @param   Registry  $params  The module parameters.
     *
     * @since   5.4.0
     *
     * @return  string    The alternate title for the module.
     */
    public function getModuleTitle($params): string
    {
        return Text::plural('MOD_LOGGED_TITLE', $params->get('count', 5));
    }
}
