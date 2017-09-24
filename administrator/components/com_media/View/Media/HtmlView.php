<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\View\Media;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Media List View
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var array|string Holds a list of providers
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $providers = null;

	/**
	 * @var string The current path of the media manager
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $currentPath;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
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
			// TODO throw an exception
		}

		// Get the current path. Providers are ordered by plugin ordering, so we set the first provider
		// in the list as the default provider and load first drive on it as default
		$this->currentPath = Factory::getApplication()->input->getString('path', $this->providers[0]->name . '-0:/');

		parent::display($tpl);
	}

	/**
	 * Prepare the toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function prepareToolbar()
	{
		$tmpl = Factory::getApplication()->input->getCmd('tmpl');

		// Get the toolbar object instance
		$bar  = Toolbar::getInstance('toolbar');
		$user = Factory::getUser();

		// Set the title
		ToolbarHelper::title(\JText::_('COM_MEDIA'), 'images mediamanager');

		// Add the upload and create folder buttons
		if ($user->authorise('core.create', 'com_media'))
		{
			// Add the upload button
			$layout = new FileLayout('toolbar.upload', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

			$bar->appendButton('Custom', $layout->render(array()), 'upload');
			ToolbarHelper::divider();

			// Add the create folder button
			$layout = new FileLayout('toolbar.create-folder', JPATH_COMPONENT_ADMINISTRATOR . '/layouts');

			$bar->appendButton('Custom', $layout->render(array()), 'new');
			ToolbarHelper::divider();
		}

		// Add a delete button
		if ($user->authorise('core.delete', 'com_media'))
		{
			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new FileLayout('toolbar.delete');

			$bar->appendButton('Custom', $layout->render(array()), 'delete');
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
			ToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER');
		}
	}
}
