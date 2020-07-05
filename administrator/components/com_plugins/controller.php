<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugins master display controller.
 *
 * @since  1.5
 */
class PluginsController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		JLoader::register('PluginsHelper', JPATH_ADMINISTRATOR . '/components/com_plugins/helpers/plugins.php');

		// Load the submenu.
		PluginsHelper::addSubmenu($this->input->get('view', 'plugins'));

		$view   = $this->input->get('view', 'plugins');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('extension_id');

		// Check for edit form.
		if ($view == 'plugin' && $layout == 'edit' && !$this->checkEditId('com_plugins.edit.plugin', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_plugins&view=plugins', false));

			return false;
		}

		parent::display();
	}
}
