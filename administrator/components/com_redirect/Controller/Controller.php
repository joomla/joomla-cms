<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Redirect\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Controller as BaseController;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

/**
 * Redirect master display controller.
 *
 * @since  1.6
 */
class Controller extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'links';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   mixed    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static	 This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Load the submenu.
		RedirectHelper::addSubmenu($this->input->get('view', 'links'));

		$view   = $this->input->get('view', 'links');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view === 'links')
		{
			$pluginEnabled      = RedirectHelper::isEnabled();
			$collectUrlsEnabled = RedirectHelper::collectUrlsEnabled();

			// Show messages about the enabled plugin and if the plugin should collect URLs
			if ($pluginEnabled && $collectUrlsEnabled)
			{
				$this->app->enqueueMessage(\JText::_('COM_REDIRECT_PLUGIN_ENABLED') . ' ' . \JText::_('COM_REDIRECT_COLLECT_URLS_ENABLED'), 'notice');
			}
			elseif ($pluginEnabled && !$collectUrlsEnabled)
			{
				$link = \JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . RedirectHelper::getRedirectPluginId());
				$this->app->enqueueMessage(\JText::_('COM_REDIRECT_PLUGIN_ENABLED') . \JText::sprintf('COM_REDIRECT_COLLECT_URLS_DISABLED', $link), 'notice');
			}
			else
			{
				$link = \JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . RedirectHelper::getRedirectPluginId());
				$this->app->enqueueMessage(\JText::sprintf('COM_REDIRECT_PLUGIN_DISABLED', $link), 'error');
			}
		}

		// Check for edit form.
		if ($view == 'link' && $layout == 'edit' && !$this->checkEditId('com_redirect.edit.link', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(\JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(\JRoute::_('index.php?option=com_redirect&view=links', false));

			return false;
		}

		parent::display();
	}
}
