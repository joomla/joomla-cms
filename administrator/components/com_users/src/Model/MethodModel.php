<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Event\MultiFactor\GetSetup;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multi-factor Authentication management model
 *
 * @since 4.2.0
 */
class MethodModel extends BaseDatabaseModel
{
    /**
     * List of MFA Methods
     *
     * @var   array
     * @since 4.2.0
     */
    protected $mfaMethods = null;

    /**
     * Get the specified MFA Method's record
     *
     * @param   string  $method  The Method to retrieve.
     *
     * @return  array
     * @since 4.2.0
     */
    public function getMethod(string $method): array
    {
        if (!$this->methodExists($method)) {
            return [
                'name'          => $method,
                'display'       => '',
                'shortinfo'     => '',
                'image'         => '',
                'canDisable'    => true,
                'allowMultiple' => true,
            ];
        }

        return $this->mfaMethods[$method];
    }

    /**
     * Is the specified MFA Method available?
     *
     * @param   string  $method  The Method to check.
     *
     * @return  boolean
     * @since 4.2.0
     */
    public function methodExists(string $method): bool
    {
        if (!is_array($this->mfaMethods)) {
            $this->populateMfaMethods();
        }

        return isset($this->mfaMethods[$method]);
    }

    /**
     * @param   User|null  $user  The user record. Null to use the currently logged in user.
     *
     * @return  array
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function getRenderOptions(?User $user = null): SetupRenderOptions
    {
        if (is_null($user)) {
            $user = Factory::getApplication()->getIdentity() ?: $this->getCurrentUser();
        }

        $renderOptions = new SetupRenderOptions();

        $event    = new GetSetup($this->getRecord($user));
        $results  = Factory::getApplication()
            ->getDispatcher()
            ->dispatch($event->getName(), $event)
            ->getArgument('result', []);

        if (empty($results)) {
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
     * Get the specified MFA record. It will return a fake default record when no record ID is specified.
     *
     * @param   User|null  $user  The user record. Null to use the currently logged in user.
     *
     * @return  MfaTable
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    public function getRecord(User $user = null): MfaTable
    {
        if (is_null($user)) {
            $user = $this->getCurrentUser();
        }

        $defaultRecord = $this->getDefaultRecord($user);
        $id            = (int) $this->getState('id', 0);

        if ($id <= 0) {
            return $defaultRecord;
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
            return $defaultRecord;
        }

        if (!$this->methodExists($record->method)) {
            return $defaultRecord;
        }

        return $record;
    }

    /**
     * Return the title to use for the page
     *
     * @return  string
     *
     * @since 4.2.0
     */
    public function getPageTitle(): string
    {
        $task = $this->getState('task', 'edit');

        switch ($task) {
            case 'mfa':
                $key = 'COM_USERS_USER_MULTIFACTOR_AUTH';
                break;

            default:
                $key = sprintf('COM_USERS_MFA_%s_PAGE_HEAD', $task);
                break;
        }

        return Text::_($key);
    }

    /**
     * @param   User|null  $user  The user record. Null to use the current user.
     *
     * @return  MfaTable
     * @throws  \Exception
     *
     * @since 4.2.0
     */
    protected function getDefaultRecord(?User $user = null): MfaTable
    {
        if (is_null($user)) {
            $user = $this->getCurrentUser();
        }

        $method = $this->getState('method');
        $title  = '';

        if (is_null($this->mfaMethods)) {
            $this->populateMfaMethods();
        }

        if ($method && isset($this->mfaMethods[$method])) {
            $title = $this->mfaMethods[$method]['display'];
        }

        /** @var MfaTable $record */
        $record = $this->getTable('Mfa', 'Administrator');

        $record->bind(
            [
                'id'      => null,
                'user_id' => $user->id,
                'title'   => $title,
                'method'  => $method,
                'default' => 0,
                'options' => [],
            ]
        );

        return $record;
    }

    /**
     * Populate the list of MFA Methods
     *
     * @return void
     * @since 4.2.0
     */
    private function populateMfaMethods(): void
    {
        $this->mfaMethods = [];
        $mfaMethods       = MfaHelper::getMfaMethods();

        if (empty($mfaMethods)) {
            return;
        }

        foreach ($mfaMethods as $method) {
            $this->mfaMethods[$method['name']] = $method;
        }

        // We also need to add the backup codes Method
        $this->mfaMethods['backupcodes'] = [
            'name'          => 'backupcodes',
            'display'       => Text::_('COM_USERS_USER_BACKUPCODES'),
            'shortinfo'     => Text::_('COM_USERS_USER_BACKUPCODES_DESC'),
            'image'         => 'media/com_users/images/emergency.svg',
            'canDisable'    => false,
            'allowMultiple' => false,
        ];
    }
}
