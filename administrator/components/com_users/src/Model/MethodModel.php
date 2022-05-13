<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Exception;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Language\Text;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Component\Users\Administrator\Table\TfaTable;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;

/**
 * Two Step Verification Method management model
 */
class MethodModel extends BaseDatabaseModel
{
	/**
	 * List of TFA Methods
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	protected $tfaMethods = null;

	/**
	 * Get the specified TFA Method's record
	 *
	 * @param   string  $method  The Method to retrieve.
	 *
	 * @return  array
	 * @since __DEPLOY_VERSION__
	 */
	public function getMethod(string $method): array
	{
		if (!$this->methodExists($method))
		{
			return [
				'name'          => $method,
				'display'       => '',
				'shortinfo'     => '',
				'image'         => '',
				'canDisable'    => true,
				'allowMultiple' => true,
			];
		}

		return $this->tfaMethods[$method];
	}

	/**
	 * Is the specified TFA Method available?
	 *
	 * @param   string  $method  The Method to check.
	 *
	 * @return  boolean
	 * @since __DEPLOY_VERSION__
	 */
	public function methodExists(string $method): bool
	{
		if (!is_array($this->tfaMethods))
		{
			$this->populateTfaMethods();
		}

		return isset($this->tfaMethods[$method]);
	}

	/**
	 * @param   User|null  $user  The user record. Null to use the currently logged in user.
	 *
	 * @return  array
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getRenderOptions(?User $user = null): SetupRenderOptions
	{
		if (is_null($user))
		{
			$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();
		}

		$renderOptions = new SetupRenderOptions;

		$results = TfaHelper::triggerEvent(new GenericEvent('onTfaGetSetup', ['record' => $this->getRecord($user)]));

		if (empty($results))
		{
			return $renderOptions;
		}

		foreach ($results as $result)
		{
			if (empty($result))
			{
				continue;
			}

			return $renderOptions->merge($result);
		}

		return $renderOptions;
	}

	/**
	 * Get the specified TFA record. It will return a fake default record when no record ID is specified.
	 *
	 * @param   User|null  $user  The user record. Null to use the currently logged in user.
	 *
	 * @return  TfaTable
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getRecord(User $user = null): TfaTable
	{
		if (is_null($user))
		{
			$user = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		}

		$defaultRecord = $this->getDefaultRecord($user);
		$id            = (int) $this->getState('id', 0);

		if ($id <= 0)
		{
			return $defaultRecord;
		}

		/** @var TfaTable $record */
		$record = $this->getTable('Tfa', 'Administrator');
		$loaded = $record->load(
			[
				'user_id' => $user->id,
				'id'      => $id,
			]
		);

		if (!$loaded)
		{
			return $defaultRecord;
		}

		if (!$this->methodExists($record->method))
		{
			return $defaultRecord;
		}

		return $record;
	}

	/**
	 * Return the title to use for the page
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getPageTitle(): string
	{
		$task = $this->getState('task', 'edit');

		switch ($task)
		{
			case 'tfa':
				$key = 'COM_USERS_USER_TWO_FACTOR_AUTH';
				break;

			default:
				$key = sprintf('COM_USERS_TFA_%s_PAGE_HEAD', $task);
				break;
		}

		return Text::_($key);
	}

	/**
	 * @param   User|null  $user  The user record. Null to use the current user.
	 *
	 * @return  TfaTable
	 * @throws  Exception
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getDefaultRecord(?User $user = null): TfaTable
	{
		if (is_null($user))
		{
			$user = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		}

		$method = $this->getState('method');
		$title  = '';

		if (is_null($this->tfaMethods))
		{
			$this->populateTfaMethods();
		}

		if ($method && isset($this->tfaMethods[$method]))
		{
			$title = $this->tfaMethods[$method]['display'];
		}

		/** @var TfaTable $record */
		$record = $this->getTable('Tfa', 'Administrator');

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
	 * Populate the list of TFA Methods
	 *
	 * @return void
	 * @since __DEPLOY_VERSION__
	 */
	private function populateTfaMethods(): void
	{
		$this->tfaMethods = [];
		$tfaMethods       = TfaHelper::getTfaMethods();

		if (empty($tfaMethods))
		{
			return;
		}

		foreach ($tfaMethods as $method)
		{
			$this->tfaMethods[$method['name']] = $method;
		}

		// We also need to add the backup codes Method
		$this->tfaMethods['backupcodes'] = [
			'name'          => 'backupcodes',
			'display'       => Text::_('COM_USERS_USER_OTEPS'),
			'shortinfo'     => Text::_('COM_USERS_USER_OTEPS_DESC'),
			'image'         => 'media/com_users/images/emergency.svg',
			'canDisable'    => false,
			'allowMultiple' => false,
		];
	}
}
