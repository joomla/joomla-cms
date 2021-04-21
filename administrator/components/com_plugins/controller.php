<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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

	public function ajaxControlPlugin()
	{
		// check token to prevent CSRF
		JSession::checkToken('get') or die( 'Invalid Token' );

		$input = JFactory::getApplication()->input;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// query to get plugin name
		$query
			->clear()
			->select('name')
			->from($db->quoteName('#__extensions'))
			->where($db->qn('extension_id') . ' = ' . $input->get('extension_id'));

		$db->setQuery($query);

		try
		{
			$plgName = $db->loadResult();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			die;
		}

		if (empty($plgName))
		{
			die;
		}

		$query = $db->getQuery(true);

		// enable or disable plugin

		$plgAction = $input->get('pluginAction') == true ? 1 : 0;

		$query
			->clear()
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled') . ' = ' . $plgAction)
			->where($db->qn('name') . ' = ' . $db->q($plgName))
			->where($db->qn('type') . ' = ' . $db->q('plugin'));

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			die;
		}

		die;
    }
}
