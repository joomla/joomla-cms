<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Captive;

use Exception;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\CaptiveModel;
use stdClass;

/**
 * View for Two Factor Authentication captive page
 *
 * @since __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The TFA Method records for the current user which correspond to enabled plugins
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	public $records = [];

	/**
	 * The currently selected TFA Method record against which we'll be authenticating
	 *
	 * @var   null|stdClass
	 * @since __DEPLOY_VERSION__
	 */
	public $record = null;

	/**
	 * The Captive TFA page's rendering options
	 *
	 * @var   array|null
	 * @since __DEPLOY_VERSION__
	 */
	public $renderOptions = null;

	/**
	 * The title to display at the top of the page
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	public $title = '';

	/**
	 * Is this an administrator page?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	public $isAdmin = false;

	/**
	 * Does the currently selected Method allow authenticating against all of its records?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	public $allowEntryBatching = false;

	/**
	 * All enabled TFA Methods (plugins)
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	public $tfaMethods;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise an Error object.
	 *
	 * @throws  Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getApplication()->getIdentity()
			?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		$app->triggerEvent(
			'onUserTwofactorBeforeDisplayMethods',
			new GenericEvent(
				'onUserTwofactorBeforeDisplayMethods',
				[
					'user' => $user,
				]
			)
		);

		/** @var CaptiveModel $model */
		$model = $this->getModel();

		// Load data from the model
		$this->isAdmin    = $app->isClient('administrator');
		$this->records    = $this->get('records');
		$this->record     = $this->get('record');
		$this->tfaMethods = TfaHelper::getTfaMethods();

		if (!empty($this->records))
		{
			/** @var BackupcodesModel $codesModel */
			$codesModel        = $this->getModel('Backupcodes');
			$backupCodesRecord = $codesModel->getBackupCodesRecord();

			if (!is_null($backupCodesRecord))
			{
				$backupCodesRecord->title = Text::_('COM_USERS_USER_OTEPS');
				$this->records[]          = $backupCodesRecord;
			}
		}

		// If we only have one record there's no point asking the user to select a TFA Method
		if (empty($this->record) && !empty($this->records))
		{
			// Default to the first record
			$this->record = reset($this->records);

			// If we have multiple records try to make this record the default
			if (count($this->records) > 1)
			{
				foreach ($this->records as $record)
				{
					if ($record->default)
					{
						$this->record = $record;

						break;
					}
				}
			}
		}

		// Set the correct layout based on the availability of a TFA record
		$this->setLayout('default');

		// If we have no record selected or explicitly asked to run the 'select' task use the correct layout
		if (is_null($this->record) || ($model->getState('task') == 'select'))
		{
			$this->setLayout('select');
		}

		switch ($this->getLayout())
		{
			case 'select':
				$this->allowEntryBatching = 1;

				$app->triggerEvent(
					'onComUsersCaptiveShowSelect',
					new GenericEvent('onComUsersCaptiveShowSelect', [])
				);
				break;

			case 'default':
			default:
				$this->renderOptions      = $model->loadCaptiveRenderOptions($this->record);
				$this->allowEntryBatching = $this->renderOptions['allowEntryBatching'] ?? 0;

				$app->triggerEvent(
					'onComUsersCaptiveShowCaptive',
					new GenericEvent(
						'onComUsersCaptiveShowCaptive',
						[
							$this->escape($this->record->title),
						]
					)
				);
				break;
		}

		// Which title should I use for the page?
		$this->title = $this->get('PageTitle');

		// Back-end: always show a title in the 'title' module position, not in the page body
		if ($this->isAdmin)
		{
			ToolbarHelper::title(Text::_('COM_USERS_HEADING_TFA'), 'users user-lock');
			$this->title = '';
		}

		// Display the view
		parent::display($tpl);
	}
}
