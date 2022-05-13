<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Methods;

use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\MethodsModel;

/**
 * View for Two Factor Authentication methods list page
 *
 * @since __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Is this an administrator page?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	public $isAdmin = false;

	/**
	 * The TFA Methods available for this user
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	public $methods = [];

	/**
	 * The return URL to use for all links and forms
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	public $returnURL = null;

	/**
	 * Are there any active TFA Methods at all?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	public $tfaActive = false;

	/**
	 * Which Method has the default record?
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	public $defaultMethod = '';

	/**
	 * The user object used to display this page
	 *
	 * @var   User
	 * @since __DEPLOY_VERSION__
	 */
	public $user = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null): void
	{
		$app = Factory::getApplication();

		if (empty($this->user))
		{
			$this->user = Factory::getApplication()->getIdentity()
				?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
		}

		/** @var MethodsModel $model */
		$model = $this->getModel();

		if ($this->getLayout() != 'firsttime')
		{
			$this->setLayout('default');
		}

		$this->methods = $model->getMethods($this->user);
		$this->isAdmin = $app->isClient('administrator');
		$activeRecords = 0;

		foreach ($this->methods as $methodName => $method)
		{
			$methodActiveRecords = count($method['active']);

			if (!$methodActiveRecords)
			{
				continue;
			}

			$activeRecords   += $methodActiveRecords;
			$this->tfaActive = true;

			foreach ($method['active'] as $record)
			{
				if ($record->default)
				{
					$this->defaultMethod = $methodName;

					break;
				}
			}
		}

		// If there are no backup codes yet we should create new ones
		/** @var BackupcodesModel $model */
		$model       = $this->getModel('backupcodes');
		$backupCodes = $model->getBackupCodes($this->user);

		if ($activeRecords && empty($backupCodes))
		{
			$model->regenerateBackupCodes($this->user);
		}

		$backupCodesRecord = $model->getBackupCodesRecord($this->user);

		if (!is_null($backupCodesRecord))
		{
			$this->methods['backupcodes'] = new MethodDescriptor(
				[
					'name'       => 'backupcodes',
					'display'    => Text::_('COM_USERS_USER_OTEPS'),
					'shortinfo'  => Text::_('COM_USERS_USER_OTEPS_DESC'),
					'image'      => 'media/com_users/images/emergency.svg',
					'canDisable' => false,
					'active'     => [$backupCodesRecord],
				]
			);
		}

		// Include CSS
		$this->document->getWebAssetManager()
			->useStyle('com_users.methods');

		// Back-end: always show a title in the 'title' module position, not in the page body
		if ($this->isAdmin)
		{
			ToolbarHelper::title(Text::_('COM_USERS_HEAD_LIST_PAGE'), 'users user-lock');
			$this->title = '';

			ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_users'));
		}

		// Display the view
		parent::display($tpl);

		TfaHelper::triggerEvent(
			new GenericEvent('onComUsersViewMethodsAfterDisplay', [$this])
		);
	}
}
