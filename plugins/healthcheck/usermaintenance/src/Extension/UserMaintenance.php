<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Healthcheck.UserMaintenance
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Healthcheck\UserMaintenance\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Healthcheck\Administrator\Event\HealthChecksEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to check usermaintenance issues
 *
 * @since    __DEPLOY_VERSION__
 */
final class UserMaintenance extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since    __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        // which of the available Healthcheck events does the subscriber listen to?
        return [
            'onHealthcheckGetIcons' => 'onHealthcheckGetIcons', //  creates JSON array of QuickIcons
        ];
    }

    /**
     * Returns the array of individual check-results in the layout of "QuickIcons"
     *
     * @param   HealthChecksEvent  $event  The health-check event object.
     *
     * @return  void
     *
     * @since    __DEPLOY_VERSION__
     */
    public function onHealthcheckGetIcons(HealthChecksEvent $event): void
    {
        $context = $event->getContext();

        if ($context !== $this->params->get('context', 'usermanagement')) {
            $this->handleErrorMsg('onHealthcheckGetIcons wrong context: ' . $context, 'WARNING');
            return;
        }

        $this->loadLanguage();

        $checks = [];

        $checks['inactiveUsers']      = $this->getInactiveUsers();
        $checks['neverloggedinUsers'] = $this->getNeverLoggedinUsers();
        $checks['unactivatedUsers']   = $this->getUnactivatedUsers();
        $checks['orphanUsers']        = $this->getOrphanUsers();
        $checks['nonMFAUsers']        = $this->getNonMFAUsers();
        $checks['privilegedUsers']    = $this->getPrivilegedUsers();

        // Add the buttons to the result array
        $result = $event->getArgument('result', []);

        $checkResults = [];
        foreach ($checks as $key => $check) {
            if (isset($check['error'])) {
                continue;
            }
            $checkResults[] = [
                'link'   => $check['link'],
                'icon'   => 'fas fa-users-gear',
                'amount' => $check['result'],
                'text'   => $check['text'],
                'id'     => 'plg_healthcheck_usermaintenance_' . strtolower($key),
                'status' => ($check['result'] > 0) ? 'warning' : 'success',
            ];
        }

        $result[] = $checkResults;

        $event->setArgument('result', $result);
    }

    /**
     * Returns the number of users who did not login within the defined timespan (see plugin options)
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getInactiveUsers(): array
    {
        $item = [];

        if ($this->params->get('inactiveUsers', '1') == '1') {
            try {
                $inactiveTimespan = (int) $this->params->get('inactiveTimespan', 180); // Days since the last login

                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_INACTIVE_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_INACTIVE_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users&filter[state]=1';

                $db = $this->getDatabase();

                $query = $db->getQuery(true)
                    ->select('COUNT(*) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('lastvisitDate') . ' < DATE_SUB(NOW(), INTERVAL :inactiveTimespan DAY)')
                    ->where($db->quoteName('lastvisitDate') . ' IS NOT NULL')
                    ->bind(':inactiveTimespan', $inactiveTimespan, ParameterType::INTEGER);

                $db->setQuery($query);

                $inactive = $db->loadObject();
                if (\is_object($inactive)) {
                    $number = $inactive->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETINACTIVE_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * Returns the number of users who never logged in
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getNeverLoggedinUsers(): array
    {
        $item = [];

        if ($this->params->get('neverloggedinUsers', '1') == '1') {
            try {
                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_NEVERLOGGEDIN_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_NEVERLOGGEDIN_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users&filter[lastvisitrange]=never';

                $db = $this->getDatabase();

                $query = $db->getQuery(true)
                    ->select('COUNT(*) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('lastvisitDate') . ' IS NULL');

                $db->setQuery($query);

                $neverLoggedin = $db->loadObject();
                if (\is_object($neverLoggedin)) {
                    $number = $neverLoggedin->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETNEVERLOGGEDIN_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * Returns the number of users who created an account, but did not activate it
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getUnactivatedUsers(): array
    {
        $item = [];

        if ($this->params->get('unactivatedUsers', '1') == '1') {
            try {
                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_UNACTIVATED_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_UNACTIVATED_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users&filter[active]=1';

                $db = $this->getDatabase();

                $query = $db->createQuery()
                    ->select('COUNT(*) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users'))
                    ->where($db->quoteName('activation') . ' <> ' . $db->quote(''))
                    ->where($db->quoteName('block') . ' = ' . $db->quote('1'));

                $db->setQuery($query);

                $unactivated = $db->loadObject();
                if (\is_object($unactivated)) {
                    $number = $unactivated->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETUNACTIVATED_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * Returns the number of users not assigned any user group
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getOrphanUsers(): array
    {
        $item = [];

        if ($this->params->get('orphanUsers', '1') == '1') {
            try {
                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_ORPHAN_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_ORPHAN_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users';

                $db = $this->getDatabase();

                $query = $db->createQuery()
                    ->select('COUNT(*) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users', 'u'))
                    ->leftJoin(
                        $db->quoteName('#__user_usergroup_map', 'm'),
                        $db->quoteName('u.id') . ' = ' . $db->quoteName('m.user_id')
                    )
                    ->where($db->quoteName('m.user_id') . ' IS NULL');

                $db->setQuery($query);

                $orphan = $db->loadObject();
                if (\is_object($orphan)) {
                    $number = $orphan->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETORPHAN_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * Returns the number of users who have no MFA method configured
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getNonMFAUsers(): array
    {
        $item = [];

        if ($this->params->get('nonMFAUsers', '1') == '1') {
            try {
                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_NONMFA_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_NONMFA_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users&filter[mfa]=0&filter[active]=0';

                $db = $this->getDatabase();

                $query = $db->getQuery(true)
                    ->select('COUNT(DISTINCT u.id) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users', 'u'))
                    ->leftJoin(
                        $db->quoteName('#__user_mfa', 'm'),
                        $db->quoteName('u.id') . ' = ' . $db->quoteName('m.user_id')
                    )
                    ->where($db->quoteName('m.id') . ' IS NULL')
                    ->where($db->quoteName('u.block') . ' = 0');

                $db->setQuery($query);

                $nonmfa = $db->loadObject();
                if (\is_object($nonmfa)) {
                    $number = $nonmfa->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETNONMFA_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * Returns the number of "high privileged" users
     *
     * @return  array  Array containing result count, link, text/note labels, or an error key.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getPrivilegedUsers(): array
    {
        $item = [];

        if ($this->params->get('privilegedUsers', '1') == '1') {
            try {
                $adminGroupIds = $this->getAdminGroupIds();

                $item['text'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_PRIVILEGED_LISTTEXT');
                $item['note'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_PRIVILEGED_LISTNOTE');
                $item['link'] = Uri::base() . 'index.php?option=com_users&view=users&filter[group_id]=8';

                if (empty($adminGroupIds)) {
                    $item['result'] = 0;
                    return $item;
                }

                $db = $this->getDatabase();

                $query = $db->getQuery(true)
                    ->select('COUNT(*) AS ' . $db->quoteName('number'))
                    ->from($db->quoteName('#__users', 'u'))
                    ->leftJoin(
                        $db->quoteName('#__user_usergroup_map', 'm'),
                        $db->quoteName('u.id') . ' = ' . $db->quoteName('m.user_id')
                    )
                    ->leftJoin(
                        $db->quoteName('#__usergroups', 'g'),
                        $db->quoteName('m.group_id') . ' = ' . $db->quoteName('g.id')
                    )
                    ->where($db->quoteName('g.id') . ' IN (' . implode(',', array_map('intval', $adminGroupIds)) . ') ');

                $db->setQuery($query);

                $privileged = $db->loadObject();
                if (\is_object($privileged)) {
                    $number = $privileged->number;
                } else {
                    $number = 0;
                }
                $item['result'] = $number;
            } catch (\Exception $e) {
                $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_GETPRIVILEGED_ERROR') . ' / ' . $e->getMessage(), 'ERROR');
                $item['error'] = $e->getMessage();
            }
        } else {
            $item['error'] = Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_CHECKISDEACTIVATED');
        }

        return $item;
    }

    /**
     * AJAX handler for directly calling the plugin, only used during testing!
     *
     * @return    void
     *
     * @since    __DEPLOY_VERSION__
     */
    public function onAjaxUserMaintenance(): void
    {
        $app           = Factory::getApplication();
        $app->mimeType = 'application/json';
        $app->setHeader('Content-Type', 'application/json; charset=utf-8');

        try {
            $this->loadLanguage();

            $checks                       = [];
            $checks['inactiveUsers']      = $this->getInactiveUsers();
            $checks['neverloggedinUsers'] = $this->getNeverLoggedinUsers();
            $checks['unactivatedUsers']   = $this->getUnactivatedUsers();
            $checks['orphanUsers']        = $this->getOrphanUsers();
            $checks['nonMFAUsers']        = $this->getNonMFAUsers();
            $checks['privilegedUsers']    = $this->getPrivilegedUsers();

            $response = [
                'success'   => true,
                'data'      => $checks,
                'count'     => \count($checks),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->handleErrorMsg(Text::_('PLG_HEALTHCHECK_USERMAINTENANCE_MSG_ERROR_AJAX') . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);

        jexit();
    }

    /**
     * Get the ids of any user group which has core.admin privileges
     *
     * @return  array  List of integer group IDs that hold core.admin permission.
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function getAdminGroupIds(): array
    {
        // We assume 7 = Administrator, 8 = Super User - but you never know...
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select($db->quoteName('rules'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('root.1'));

        $db->setQuery($query);

        $rules = json_decode($db->loadResult(), true);

        // Get groups with core.admin permission
        $adminGroupIds = [];
        if (isset($rules['core.admin'])) {
            foreach ($rules['core.admin'] as $groupId => $allowed) {
                if ((int)$allowed === 1) {
                    $adminGroupIds[] = $groupId;
                }
            }
        }

        return $adminGroupIds;
    }

    /**
     * Handle an error or warning message according to the configured logging strategy.
     *
     * @param   string  $msg       The message to log.
     * @param   string  $msgLevel  The severity level (e.g. 'ERROR', 'WARNING').
     *
     * @return  void
     *
     * @since    __DEPLOY_VERSION__
     */
    protected function handleErrorMsg(string $msg, string $msgLevel): void
    {
        $msgContext = '[' . $this->_type . '-' . $this->_name . ']';
        $logging    = $this->params->get('logging', 0);    // How to handle errors
        switch ($logging) {
            case 3: // enqueue Message
                Factory::getApplication()->enqueueMessage($msgContext . ' ' . $msg, $msgLevel);
                break;
            case 2: // log in JoomlaLog
                Log::add($msgContext . ' ' . $msg, Log::ERROR, 'plg_healthcheck_usermaintenance');
                break;
            case 1: // log in PHP error log
                error_log($msgContext . ' ' . $msg, 0);
                break;
            case 0:
            default:
                // Do not log anywhere
        }
    }
}
