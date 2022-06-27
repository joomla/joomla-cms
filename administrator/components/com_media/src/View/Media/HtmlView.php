<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\View\Media;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Media List View
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Holds a list of providers
	 *
	 * @var array|string
	 *
	 * @since   4.0.0
	 */
	protected $providers = null;

	/**
	 * The current path of the media manager
	 *
	 * @var string
	 *
	 * @since 4.0.0
	 */
	protected $currentPath;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse;
	 *                        automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		// Prepare the toolbar
		$this->prepareToolbar();

		// Get enabled adapters
		$this->providers = $this->get('Providers');

		// Check that there are providers
		if (!count($this->providers))
		{
			$link = Route::_('index.php?option=com_plugins&view=plugins&filter[folder]=filesystem');
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_MEDIA_ERROR_NO_PROVIDERS', $link), CMSApplication::MSG_WARNING);
		}

		$this->currentPath = Factory::getApplication()->input->getString('path');

		parent::display($tpl);
	}

	/**
	 * Prepare the toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function prepareToolbar()
	{
		$tmpl = Factory::getApplication()->input->getCmd('tmpl');

		// Get the toolbar object instance
		$bar  = Toolbar::getInstance('toolbar');
		$user = $this->getCurrentUser();

		// Set the title
		ToolbarHelper::title(Text::_('COM_MEDIA'), 'images mediamanager');

		// Add the upload and create folder buttons
		if ($user->authorise('core.create', 'com_media'))
		{
			// Add the upload button
			$layout = new FileLayout('toolbar.upload', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

			$bar->appendButton('Custom', $layout->render([]), 'upload');
			ToolbarHelper::divider();

			// Add the create folder button
			$layout = new FileLayout('toolbar.create-folder', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

			$bar->appendButton('Custom', $layout->render([]), 'new');
			ToolbarHelper::divider();
		}

		// Add a delete button
		if ($user->authorise('core.delete', 'com_media'))
		{
			// Instantiate a new FileLayout instance and render the layout
			$layout = new FileLayout('toolbar.delete', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

			$bar->appendButton('Custom', $layout->render([]), 'delete');
			ToolbarHelper::divider();
		}

		// Add the preferences button
		if (($user->authorise('core.admin', 'com_media') || $user->authorise('core.options', 'com_media')) && $tmpl !== 'component')
		{
			ToolbarHelper::preferences('com_media');
			ToolbarHelper::divider();
		}

		if ($tmpl !== 'component')
		{
			ToolbarHelper::help('Media');
		}
	}
}
