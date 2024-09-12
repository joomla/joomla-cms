<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\Module;
use Joomla\CMS\Event\MultiFactor\Captive;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Captive Multi-factor Authentication page's model
 *
 * @since 4.2.0
 */
class CaptiveModel extends BaseDatabaseModel
{
    /**
     * Cache of the names of the currently active MFA Methods
     *
     * @var  array|null
     * @since 4.2.0
     */
    protected $activeMFAMethodNames = null;

    /**
     * Prevents Joomla from displaying any modules.
     *
     * This is implemented with a trick. If you use jdoc tags to load modules the JDocumentRendererHtmlModules
     * uses JModuleHelper::getModules() to load the list of modules to render. This goes through JModuleHelper::load()
     * which triggers the onAfterModuleList event after cleaning up the module list from duplicates. By resetting
     * the list to an empty array we force Joomla to not display any modules.
     *
     * Similar code paths are followed by any canonical code which tries to load modules. So even if your template does
     * not use jdoc tags this code will still work as expected.
     *
     * @param   ?CMSApplication  $app  The CMS application to manipulate
     *
     * @return  void
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function suppressAllModules(?CMSApplication $app = null): void
    {
        if (\is_null($app)) {
            $app = Factory::getApplication();
        }

        $app->registerEvent('onAfterModuleList', [$this, 'onAfterModuleList']);
    }

    /**
     * Get the MFA records for the user which correspond to active plugins
     *
     * @param   ?User  $user                The user for which to fetch records. Skip to use the current user.
     * @param   bool   $includeBackupCodes  Should I include the backup codes record?
     *
     * @return  array
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function getRecords(?User $user = null, bool $includeBackupCodes = false): array
    {
        if (\is_null($user)) {
            $user = $this->getCurrentUser();
        }

        // Get the user's MFA records
        $records = MfaHelper::getUserMfaRecords($user->id);

        // No MFA Methods? Then we obviously don't need to display a Captive login page.
        if (empty($records)) {
            return [];
        }

        // Get the enabled MFA Methods' names
        $methodNames = $this->getActiveMethodNames();

        // Filter the records based on currently active MFA Methods
        $ret = [];

        $methodNames[] = 'backupcodes';
        $methodNames   = array_unique($methodNames);

        if (!$includeBackupCodes) {
            $methodNames = array_filter(
                $methodNames,
                function ($method) {
                    return $method != 'backupcodes';
                }
            );
        }

        foreach ($records as $record) {
            // Backup codes must not be included in the list. We add them in the View, at the end of the list.
            if (\in_array($record->method, $methodNames)) {
                $ret[$record->id] = $record;
            }
        }

        return $ret;
    }

    /**
     * Return all the active MFA Methods' names
     *
     * @return  array
     * @since 4.2.0
     */
    private function getActiveMethodNames(): ?array
    {
        if (!\is_null($this->activeMFAMethodNames)) {
            return $this->activeMFAMethodNames;
        }

        // Let's get a list of all currently active MFA Methods
        $mfaMethods = MfaHelper::getMfaMethods();

        // If no MFA Method is active we can't really display a Captive login page.
        if (empty($mfaMethods)) {
            $this->activeMFAMethodNames = [];

            return $this->activeMFAMethodNames;
        }

        // Get a list of just the Method names
        $this->activeMFAMethodNames = [];

        foreach ($mfaMethods as $mfaMethod) {
            $this->activeMFAMethodNames[] = $mfaMethod['name'];
        }

        return $this->activeMFAMethodNames;
    }

    /**
     * Get the currently selected MFA record for the current user. If the record ID is empty, it does not correspond to
     * the currently logged in user or does not correspond to an active plugin null is returned instead.
     *
     * @param   User|null  $user  The user for which to fetch records. Skip to use the current user.
     *
     * @return  MfaTable|null
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function getRecord(?User $user = null): ?MfaTable
    {
        $id = (int) $this->getState('record_id', null);

        if ($id <= 0) {
            return null;
        }

        if (\is_null($user)) {
            $user = $this->getCurrentUser();
        }

        /** @var MfaTable $record */
        $record = $this->getTable('Mfa', 'Administrator');
        $loaded = $record->load(
            [
                'user_id' => $user->id,
                'id'      => $id,
            ]
        );

        if (!$loaded) {
            return null;
        }

        $methodNames = $this->getActiveMethodNames();

        if (!\in_array($record->method, $methodNames) && ($record->method != 'backupcodes')) {
            return null;
        }

        return $record;
    }

    /**
     * Load the Captive login page render options for a specific MFA record
     *
     * @param   ?MfaTable  $record  The MFA record to process
     *
     * @return  CaptiveRenderOptions  The rendering options
     * @since 4.2.0
     */
    public function loadCaptiveRenderOptions(?MfaTable $record): CaptiveRenderOptions
    {
        $renderOptions = new CaptiveRenderOptions();

        if (empty($record)) {
            return $renderOptions;
        }

        $event   = new Captive($record);
        $results = Factory::getApplication()
            ->getDispatcher()
            ->dispatch($event->getName(), $event)
            ->getArgument('result', []);

        if (empty($results)) {
            if ($record->method === 'backupcodes') {
                return $renderOptions->merge(
                    [
                        'pre_message' => Text::_('COM_USERS_USER_BACKUPCODES_CAPTIVE_PROMPT'),
                        'input_type'  => 'number',
                        'label'       => Text::_('COM_USERS_USER_BACKUPCODE'),
                    ]
                );
            }

            return $renderOptions;
        }

        foreach ($results as $result) {
            if (empty($result)) {
                continue;
            }

            return $renderOptions->merge($result);
        }

        return $renderOptions;
    }

    /**
     * Returns the title to display in the Captive login page, or an empty string if no title is to be displayed.
     *
     * @return  string
     * @since 4.2.0
     */
    public function getPageTitle(): string
    {
        // In the frontend we can choose if we will display a title
        $showTitle = (bool) ComponentHelper::getParams('com_users')
            ->get('frontend_show_title', 1);

        if (!$showTitle) {
            return '';
        }

        return Text::_('COM_USERS_USER_MULTIFACTOR_AUTH');
    }

    /**
     * Translate a MFA Method's name into its human-readable, display name
     *
     * @param   string  $name  The internal MFA Method name
     *
     * @return  string
     * @since 4.2.0
     */
    public function translateMethodName(string $name): string
    {
        static $map = null;

        if (!\is_array($map)) {
            $map        = [];
            $mfaMethods = MfaHelper::getMfaMethods();

            if (!empty($mfaMethods)) {
                foreach ($mfaMethods as $mfaMethod) {
                    $map[$mfaMethod['name']] = $mfaMethod['display'];
                }
            }
        }

        if ($name == 'backupcodes') {
            return Text::_('COM_USERS_USER_BACKUPCODES');
        }

        return $map[$name] ?? $name;
    }

    /**
     * Translate a MFA Method's name into the relative URL if its logo image
     *
     * @param   string  $name  The internal MFA Method name
     *
     * @return  string
     * @since 4.2.0
     */
    public function getMethodImage(string $name): string
    {
        static $map = null;

        if (!\is_array($map)) {
            $map        = [];
            $mfaMethods = MfaHelper::getMfaMethods();

            if (!empty($mfaMethods)) {
                foreach ($mfaMethods as $mfaMethod) {
                    $map[$mfaMethod['name']] = $mfaMethod['image'];
                }
            }
        }

        if ($name == 'backupcodes') {
            return 'media/com_users/images/emergency.svg';
        }

        return $map[$name] ?? $name;
    }

    /**
     * Process the modules list on Joomla! 4.
     *
     * Joomla! 4.x is passing an Event object. The first argument of the event object is the array of modules. After
     * filtering it we have to overwrite the event argument (NOT just return the new list of modules). If a future
     * version of Joomla! uses immutable events we'll have to use Reflection to do that or Joomla! would have to fix
     * the way this event is handled, taking its return into account. For now, we just abuse the mutable event
     * properties - a feature of the event objects we discussed in the Joomla! 4 Working Group back in August 2015.
     *
     * @param   Module\AfterModuleListEvent  $event  The Joomla! event object
     *
     * @return  void
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function onAfterModuleList(Module\AfterModuleListEvent $event): void
    {
        $modules = $event->getModules();

        if (empty($modules)) {
            return;
        }

        $this->filterModules($modules);
        $event->updateModules($modules);
    }

    /**
     * This is the Method which actually filters the sites modules based on the allowed module positions specified by
     * the user.
     *
     * @param   array  $modules  The list of the site's modules. Passed by reference.
     *
     * @return  void  The by-reference value is modified instead.
     * @since 4.2.0
     * @throws  \Exception
     */
    private function filterModules(array &$modules): void
    {
        $allowedPositions = $this->getAllowedModulePositions();

        if (empty($allowedPositions)) {
            $modules = [];

            return;
        }

        $filtered = [];

        foreach ($modules as $module) {
            if (\in_array($module->position, $allowedPositions)) {
                $filtered[] = $module;
            }
        }

        $modules = $filtered;
    }

    /**
     * Get a list of module positions we are allowed to display
     *
     * @return  array
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    private function getAllowedModulePositions(): array
    {
        $isAdmin = Factory::getApplication()->isClient('administrator');

        // Load the list of allowed module positions from the component's settings. May be different for front- and back-end
        $configKey = 'allowed_positions_' . ($isAdmin ? 'backend' : 'frontend');
        $res       = ComponentHelper::getParams('com_users')->get($configKey, []);

        // In the backend we must always add the 'title' module position
        if ($isAdmin) {
            $res[] = 'title';
            $res[] = 'toolbar';
        }

        return $res;
    }

    /**
     * Method to check if the mfa method in question has reached it's usage limit
     *
     * @param   MfaTable  $method  Mfa method record
     *
     * @return  boolean true if user can use the method, false if not
     *
     * @since    4.3.2
     * @throws  \Exception
     */
    public function checkTryLimit(MfaTable $method)
    {
        $params     = ComponentHelper::getParams('com_users');
        $jNow       = Date::getInstance();
        $maxTries   = (int) $params->get('mfatrycount', 10);
        $blockHours = (int) $params->get('mfatrytime', 1);

        $lastTryTime       = strtotime($method->last_try) ?: 0;
        $hoursSinceLastTry = (strtotime(Factory::getDate()->toSql()) - $lastTryTime) / 3600;

        if ($method->last_try !== null && $hoursSinceLastTry > $blockHours) {
            // If it's been long enough, start a new reset count
            $method->last_try = null;
            $method->tries    = 0;
        } elseif ($method->tries < $maxTries) {
            // If we are under the max count, just increment the counter
            ++$method->tries;
            $method->last_try = $jNow->toSql();
        } else {
            // At this point, we know we have exceeded the maximum resets for the time period
            return false;
        }

        // Store changes to try counter and/or the timestamp
        $method->store();

        return true;
    }
}
