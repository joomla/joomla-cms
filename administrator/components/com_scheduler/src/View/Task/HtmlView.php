<?php
/**
 * Declares the MVC View for the Task form.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\View\Cronjob;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use function defined;

/**
 * The MVC View for the Task form
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var AdministratorApplication $app
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The Form object
	 *
	 * @var Form
	 * @since  __DEPLOY_VERSION__
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var CMSObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  CMSObject
	 * @since  __DEPLOY__VERSION__
	 */
	protected $canDo;

	/**
	 * Overloads the parent constructor.
	 * Just needed to fetch the Application object.
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).
	 *                          charset: the character set to use for display
	 *                          escape: the name (optional) of the function to use for escaping strings
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
	 *                          layout: the layout (optional) to use to display the view
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		$this->app = Factory::getApplication();
		parent::__construct($config);
	}

	/**
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null): void
	{
		/*
		 * Will call the getForm() method of TaskModel
		 */
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_scheduler', 'cronjob', $this->item->id);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function addToolbar(): void
	{
		$app = Factory::getApplication();

		$app->getInput()->set('hidemainmenu', true);
		$user = $app->getIdentity();
		$userId = $user->id;
		$isNew = ($this->item->id == 0);
		$canDo = $this->canDo;

		ToolbarHelper::title($isNew ? Text::_('COM_SCHEDULER_MANAGER_TASK_NEW') : Text::_('COM_SCHEDULER_MANAGER_TASK_EDIT'), 'clock');

		// Goes into ToolbarHelper::saveGroup()
		$toolbarButtons = [];

		// For a new cronjob, check if user has 'core.create' access
		if ($isNew && $canDo->get('core.create'))
		{
			// The cronjob.apply task maps to the save() method in TaskController
			ToolbarHelper::apply('cronjob.apply');

			$toolbarButtons[] = ['save', 'cronjob.save'];
		}
		else
		{
			if (!$isNew && $canDo->get('core.edit'))
			{
				ToolbarHelper::apply('cronjob.apply');
				$toolbarButtons[] = ['save', 'cronjob.save'];

				// @todo | ? : Do we need save2new and save2copy? If yes, need to support in the Model,
				// 			  here and the Controller.
			}
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons
		);

		ToolbarHelper::cancel('cronjob.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

}
