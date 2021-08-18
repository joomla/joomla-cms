<?php
/**
 * Declares the MVC View for Cronjob type selection.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\View\Select;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Cronjobs\Administrator\Cronjobs\CronOption;
use function defined;

/**
 * The MVC View Select
 * Lets the user choose from a list of plugin defined Job routines.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var AdministratorApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The model state
	 *
	 * @var  CMSObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * An array of items
	 *
	 * @var  CronOption[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * A suffix for links for modal use [?]
	 *
	 * @var  string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $modalLink;

	/**
	 * HtmlView constructor.
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
	public function __construct($config = [])
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
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->modalLink = '';

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function addToolbar(): void
	{
		$canDo = ContentHelper::getActions('com_cronjobs');

		/*
		* Get the global Toolbar instance
		* TODO : Replace usage with ToolbarFactoryInterface. but how?
		 *       Probably some changes in the core, since mod_menu calls and renders the getInstance() toolbar
		*/
		$toolbar = Toolbar::getInstance();

		 // Add page title
		ToolbarHelper::title(Text::_('COM_CRONJOBS_MANAGER_CRONJOBS'), 'clock');

		$toolbar->linkButton('cancel')
			->url('index.php?option=com_cronjobs')
			->buttonClass('btn btn-danger')
			->icon('icon-times')
			->text(Text::_('JCANCEL'));

		// Add preferences button if user has privileges
		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_cronjobs');
		}

		// Add help button
		$toolbar->help('JHELP_COMPONENTS_CRONJOBS_MANAGER');

	}
}
