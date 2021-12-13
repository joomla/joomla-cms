<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\View\Task;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * The MVC View for Task configuration page (TaskView).
 *
 * @since  4.1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var AdministratorApplication $app
	 * @since  4.1.0
	 */
	protected $app;

	/**
	 * The Form object
	 *
	 * @var Form
	 * @since  4.1.0
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var object
	 * @since  4.1.0
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var CMSObject
	 * @since  4.1.0
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
	 *                          base_path: the parent path (optional) of the `views` directory (defaults to the
	 *                          component folder) template_plath: the path (optional) of the layout directory (defaults
	 *                          to base_path + /views/ + view name helper_path: the path (optional) of the helper files
	 *                          (defaults to base_path + /helpers/) layout: the layout (optional) to use to display the
	 *                          view
	 *
	 * @since  4.1.0
	 * @throws \Exception
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
	 * @since  4.1.0
	 * @throws \Exception
	 */
	public function display($tpl = null): void
	{
		/*
		 * Will call the getForm() method of TaskModel
		 */
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = ContentHelper::getActions('com_scheduler', 'task', $this->item->id);

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar
	 *
	 * @return void
	 *
	 * @since  4.1.0
	 */
	protected function addToolbar(): void
	{
		$app = $this->app;

		$app->getInput()->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$canDo = $this->canDo;

		/*
		 * Get the toolbar object instance
		 * !! @todo : Replace usage with ToolbarFactoryInterface
		 */
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title($isNew ? Text::_('COM_SCHEDULER_MANAGER_TASK_NEW') : Text::_('COM_SCHEDULER_MANAGER_TASK_EDIT'), 'clock');

		if (($isNew && $canDo->get('core.create')) || (!$isNew && $canDo->get('core.edit')))
		{
			$toolbar->apply('task.apply');
			$toolbar->save('task.save');
		}

		// @todo | ? : Do we need save2new, save2copy?

		$toolbar->cancel('task.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
		$toolbar->help('JHELP_COMPONENTS_SCHEDULED_TASKS_MANAGER');
	}
}
