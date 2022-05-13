<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Method;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Model\MethodModel;

/**
 * View for Two Factor Authentication method add/edit page
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
	 * The editor page render options
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	public $renderOptions = [];

	/**
	 * The TFA Method record being edited
	 *
	 * @var   object
	 * @since __DEPLOY_VERSION__
	 */
	public $record = null;

	/**
	 * The title text for this page
	 *
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	public $title = '';

	/**
	 * The return URL to use for all links and forms
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	public $returnURL = null;

	/**
	 * The user object used to display this page
	 *
	 * @var   User
	 * @since __DEPLOY_VERSION__
	 */
	public $user = null;

	/**
	 * The backup codes for the current user. Only applies when the backup codes record is being "edited"
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	public $backupCodes = [];

	/**
	 * Am I editing an existing Method? If it's false then I'm adding a new Method.
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	public $isEditExisting = false;

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

		/** @var MethodModel $model */
		$model = $this->getModel();
		$this->setLayout('edit');
		$this->renderOptions = $model->getRenderOptions($this->user);
		$this->record        = $model->getRecord($this->user);
		$this->title         = $model->getPageTitle();
		$this->isAdmin       = $app->isClient('administrator');

		// Backup codes are a special case, rendered with a special layout
		if ($this->record->method == 'backupcodes')
		{
			$this->setLayout('backupcodes');

			$backupCodes = $this->record->options;

			if (!is_array($backupCodes))
			{
				$backupCodes = [];
			}

			$backupCodes = array_filter(
				$backupCodes,
				function ($x) {
					return !empty($x);
				}
			);

			if (count($backupCodes) % 2 != 0)
			{
				$backupCodes[] = '';
			}

			/**
			 * The call to array_merge resets the array indices. This is necessary since array_filter kept the indices,
			 * meaning our elements are completely out of order.
			 */
			$this->backupCodes = array_merge($backupCodes);
		}

		// Set up the isEditExisting property.
		$this->isEditExisting = !empty($this->record->id);

		// Back-end: always show a title in the 'title' module position, not in the page body
		if ($this->isAdmin)
		{
			ToolbarHelper::title($this->title, 'users user-lock');

			$helpUrl = $this->renderOptions['help_url'];

			if (!empty($helpUrl))
			{
				ToolbarHelper::help('', false, $helpUrl);
			}

			$this->title = '';
		}

		// Display the view
		parent::display($tpl);
	}
}
