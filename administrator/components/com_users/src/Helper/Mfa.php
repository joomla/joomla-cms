<?php

/**
 * @package        Joomla.Administrator
 * @subpackage     com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\MultiFactor\GetMethod;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\MethodsModel;
use Joomla\Component\Users\Administrator\Table\MfaTable;
use Joomla\Component\Users\Administrator\View\Methods\HtmlView;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper functions for captive MFA handling
 *
 * @since 4.2.0
 */
abstract class Mfa
{
    /**
     * Cache of all currently active MFAs
     *
     * @var   array|null
     * @since 4.2.0
     */
    protected static $allMFAs = null;

    /**
     * Are we inside the administrator application
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected static $isAdmin = null;

    /**
     * Get the HTML for the Multi-factor Authentication configuration interface for a user.
     *
     * This helper method uses a sort of primitive HMVC to display the com_users' Methods page which
     * renders the MFA configuration interface.
     *
     * @param   User  $user  The user we are going to show the configuration UI for.
     *
     * @return  string|null  The HTML of the UI; null if we cannot / must not show it.
     * @throws  \Exception
     * @since   4.2.0
     */
    public static function getConfigurationInterface(User $user): ?string
    {
        // Check the conditions
        if (!self::canShowConfigurationInterface($user)) {
            return null;
        }

        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        if ($app->getInput()->getCmd('option', '') !== 'com_users') {
            $app->getLanguage()->load('com_users');
            $app->getDocument()
                ->getWebAssetManager()
                ->getRegistry()
                ->addExtensionRegistryFile('com_users');
        }

        // Get a model
        /** @var MVCFactoryInterface $factory */
        $factory = Factory::getApplication()->bootComponent('com_users')->getMVCFactory();

        /** @var MethodsModel $methodsModel */
        $methodsModel = $factory->createModel('Methods', 'Administrator');
        /** @var BackupcodesModel $methodsModel */
        $backupCodesModel = $factory->createModel('Backupcodes', 'Administrator');

        // Get a view object
        $appRoot = $app->isClient('site') ? \JPATH_SITE : \JPATH_ADMINISTRATOR;
        $prefix  = $app->isClient('site') ? 'Site' : 'Administrator';
        /** @var HtmlView $view */
        $view = $factory->createView(
            'Methods',
            $prefix,
            'Html',
            [
                'base_path' => $appRoot . '/components/com_users',
            ]
        );
        $view->setModel($methodsModel, true);
        /** @noinspection PhpParamsInspection */
        $view->setModel($backupCodesModel);
        $view->setDocument($app->getDocument());
        $view->returnURL = base64_encode(Uri::getInstance()->toString());
        $view->user      = $user;
        $view->set('forHMVC', true);
        $view->setLanguage($app->getLanguage());

        @ob_start();

        try {
            $view->display();
        } catch (\Throwable $e) {
            @ob_end_clean();

            /**
             * This is intentional! When you are developing a Multi-factor Authentication plugin you
             * will inevitably mess something up and end up with an error. This would cause the
             * entire MFA configuration page to disappear. No problem! Set Debug System to Yes in
             * Global Configuration and you can see the error exception which will help you solve
             * your problem.
             */
            if (\defined('JDEBUG') && JDEBUG) {
                throw $e;
            }

            return null;
        }

        return @ob_get_clean();
    }

    /**
     * Get a list of all of the MFA Methods
     *
     * @return  MethodDescriptor[]
     * @since 4.2.0
     */
    public static function getMfaMethods(): array
    {
        PluginHelper::importPlugin('multifactorauth');

        if (\is_null(self::$allMFAs)) {
            // Get all the plugin results
            $event = new GetMethod();
            $temp  = Factory::getApplication()
                ->getDispatcher()
                ->dispatch($event->getName(), $event)
                ->getArgument('result', []);

            // Normalize the results
            self::$allMFAs = [];

            foreach ($temp as $method) {
                if (!\is_array($method) && !($method instanceof MethodDescriptor)) {
                    continue;
                }

                $method = $method instanceof MethodDescriptor
                    ? $method : new MethodDescriptor($method);

                if (empty($method['name'])) {
                    continue;
                }

                self::$allMFAs[$method['name']] = $method;
            }
        }

        return self::$allMFAs;
    }

    /**
     * Is the current user allowed to add/edit MFA methods for $user?
     *
     * This is only allowed if I am adding / editing methods for myself.
     *
     * If the target user is a member of any group disallowed to use MFA this will return false.
     *
     * @param   User|null  $user  The user you want to know if we're allowed to edit
     *
     * @return  boolean
     * @throws  \Exception
     * @since 4.2.0
     */
    public static function canAddEditMethod(?User $user = null): bool
    {
        // Cannot do MFA operations on no user or a guest user.
        if (\is_null($user) || $user->guest) {
            return false;
        }

        // If the user is in a user group which disallows MFA we cannot allow adding / editing methods.
        $neverMFAGroups = ComponentHelper::getParams('com_users')->get('neverMFAUserGroups', []);
        $neverMFAGroups = \is_array($neverMFAGroups) ? $neverMFAGroups : [];

        if (\count(array_intersect($user->getAuthorisedGroups(), $neverMFAGroups))) {
            return false;
        }

        // Check if this is the same as the logged-in user.
        $myUser = Factory::getApplication()->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        return $myUser->id === $user->id;
    }

    /**
     * Is the current user allowed to delete MFA methods / disable MFA for $user?
     *
     * This is allowed if:
     * - The user being queried is the same as the logged-in user
     * - The logged-in user is a Super User AND the queried user is NOT a Super User.
     *
     * Note that Super Users can be edited by their own user only for security reasons. If a Super
     * User gets locked out they must use the Backup Codes to regain access. If that's not possible,
     * they will need to delete their records from the `#__user_mfa` table.
     *
     * @param   User|null  $user  The user being queried.
     *
     * @return  boolean
     * @throws  \Exception
     * @since   4.2.0
     */
    public static function canDeleteMethod(?User $user = null): bool
    {
        // Cannot do MFA operations on no user or a guest user.
        if (\is_null($user) || $user->guest) {
            return false;
        }

        $myUser = Factory::getApplication()->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        return $myUser->id === $user->id
            || ($myUser->authorise('core.admin') && !$user->authorise('core.admin'));
    }

    /**
     * Return all MFA records for a specific user
     *
     * @param   int|null  $userId  User ID. NULL for currently logged in user.
     *
     * @return  MfaTable[]
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public static function getUserMfaRecords(?int $userId): array
    {
        if (empty($userId)) {
            $user   = Factory::getApplication()->getIdentity() ?: Factory::getUser();
            $userId = $user->id ?: 0;
        }

        /** @var DatabaseInterface $db */
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__user_mfa'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->bind(':user_id', $userId, ParameterType::INTEGER);

        try {
            $ids = $db->setQuery($query)->loadColumn() ?: [];
        } catch (\Exception $e) {
            $ids = [];
        }

        if (empty($ids)) {
            return [];
        }

        /** @var MVCFactoryInterface $factory */
        $factory = Factory::getApplication()->bootComponent('com_users')->getMVCFactory();

        // Map all results to MFA table objects
        $records = array_map(
            function ($id) use ($factory) {
                /** @var MfaTable $record */
                $record = $factory->createTable('Mfa', 'Administrator');
                $loaded = $record->load($id);

                return $loaded ? $record : null;
            },
            $ids
        );

        // Let's remove Methods we couldn't decrypt when reading from the database.
        $hasBackupCodes = false;

        $records = array_filter(
            $records,
            function ($record) use (&$hasBackupCodes) {
                $isValid = !\is_null($record) && (!empty($record->options));

                if ($isValid && ($record->method === 'backupcodes')) {
                    $hasBackupCodes = true;
                }

                return $isValid;
            }
        );

        // If the only Method is backup codes it's as good as having no records
        if ((\count($records) === 1) && $hasBackupCodes) {
            return [];
        }

        return $records;
    }

    /**
     * Are the conditions for showing the MFA configuration interface met?
     *
     * @param   User|null  $user  The user to be configured
     *
     * @return  boolean
     * @throws  \Exception
     * @since 4.2.0
     */
    public static function canShowConfigurationInterface(?User $user = null): bool
    {
        // If I have no user to check against that's all the checking I can do.
        if (empty($user)) {
            return false;
        }

        // I need at least one MFA method plugin for the setup interface to make any sense.
        $plugins = PluginHelper::getPlugin('multifactorauth');

        if (\count($plugins) < 1) {
            return false;
        }

        /** @var CMSApplication $app */
        $app = Factory::getApplication();

        // We can only show a configuration page in the front- or backend application.
        if (!$app->isClient('site') && !$app->isClient('administrator')) {
            return false;
        }

        // Only show the configuration page if we have an HTML document
        if (!($app->getDocument() instanceof HtmlDocument)) {
            return false;
        }

        // I must be able to add, edit or delete the user's MFA settings
        return self::canAddEditMethod($user) || self::canDeleteMethod($user);
    }
}
