<?php
/**
 * Declares the MVC View for SelectPluginModel.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\View\Select;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * The MVC View Select
 * Should let the user choose from a list of plugin defined Jobs or a CLI job.
 * ! : Untested
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @since      __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  \JObject
	 * @since __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * An array of items [TODO: Implement a 'Job' object or similar]
	 *
	 * @var  array
	 * @since __DEPLOY_VERSION__
	 */
	protected $items;

	/**
	 * Will be used for the "CLI" / "Script" type job
	 *
	 * @var object
	 * @since version
	 */
	protected $specialItem;

	/**
	 * A suffix for links for modal use [?]
	 *
	 * @var  string
	 * @since __DEPLOY_VERSION__
	 */
	protected $modalLink;


	/**
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function display($tpl = null): void
	{
		// ! Untested

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
	 * ! : Untested
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$clientId = (int) $state->get('client_id', 0);

		/*
		 * Add page title
		 * TODO: 'cronjobs' icon
		 */
		ToolbarHelper::title(Text::_('COM_CRONJOBS_MANAGER_CRONJOBS'), 'tags');


		/*
		 * Get the toolbar object instance
		 * TODO : Replace usage with ToolbarFactoryInterface
		 */
		$bar = Toolbar::getInstance('toolbar');

		// Instantiate a new FileLayout instance and render the layout
		$layout = new FileLayout('toolbar.cancelselect');

		/*
		 * ? : What is this doing?
		 * ! : appendButton seems to want a ToolbarButton object, but we're passing a string?
		 * TODO : Button object?
		 */
		$bar->appendButton('Custom', $layout->render(array('client_id' => $clientId)), 'new');
	}
}
