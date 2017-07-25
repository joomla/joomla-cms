<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Helper;

defined('_JEXEC') or die;

/**
 * Installer helper.
 *
 * @since  1.6
 */
class InstallerHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 */
	public static function addSubmenu($vName = 'install')
	{
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_INSTALL'),
			'index.php?option=com_installer',
			$vName == 'install'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_UPDATE'),
			'index.php?option=com_installer&view=update',
			$vName == 'update'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_MANAGE'),
			'index.php?option=com_installer&view=manage',
			$vName == 'manage'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_DISCOVER'),
			'index.php?option=com_installer&view=discover',
			$vName == 'discover'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_DATABASE'),
			'index.php?option=com_installer&view=database',
			$vName == 'database'
		);
		\JHtmlSidebar::addEntry(
		\JText::_('COM_INSTALLER_SUBMENU_WARNINGS'),
			'index.php?option=com_installer&view=warnings',
			$vName == 'warnings'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_LANGUAGES'),
			'index.php?option=com_installer&view=languages',
			$vName == 'languages'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_UPDATESITES'),
			'index.php?option=com_installer&view=updatesites',
			$vName == 'updatesites'
		);
		\JHtmlSidebar::addEntry(
			\JText::_('COM_INSTALLER_SUBMENU_DOWNLOADKEYS'),
			'index.php?option=com_installer&view=downloadkeys',
			$vName == 'downloadkeys'
		);
	}

	/**
	 * Get a list of filter options for the extension types.
	 *
	 * @return  array  An array of \stdClass objects.
	 *
	 * @since   3.0
	 */
	public static function getExtensionTypes()
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT type')
			->from('#__extensions');
		$db->setQuery($query);
		$types = $db->loadColumn();

		$options = array();

		foreach ($types as $type)
		{
			$options[] = \JHtml::_('select.option', $type, \JText::_('COM_INSTALLER_TYPE_' . strtoupper($type)));
		}

		return $options;
	}

	/**
	 * Get a list of filter options for the extension types.
	 *
	 * @return  array  An array of \stdClass objects.
	 *
	 * @since   3.0
	 */
	public static function getExtensionGroupes()
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT folder')
			->from('#__extensions')
			->where('folder != ' . $db->quote(''))
			->order('folder');
		$db->setQuery($query);
		$folders = $db->loadColumn();

		$options = array();

		foreach ($folders as $folder)
		{
			$options[] = \JHtml::_('select.option', $folder, $folder);
		}

		return $options;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return  array  An array of \JHtmlOption elements.
	 *
	 * @since   3.5
	 */
	public static function getClientOptions()
	{
		// Build the filter options.
		$options   = array();
		$options[] = \JHtml::_('select.option', '0', \JText::_('JSITE'));
		$options[] = \JHtml::_('select.option', '1', \JText::_('JADMINISTRATOR'));

		return $options;
	}

	/**
	 * Get a list of filter options for the application statuses.
	 *
	 * @return  array  An array of \JHtmlOption elements.
	 *
	 * @since   3.5
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options   = array();
		$options[] = \JHtml::_('select.option', '0', \JText::_('JDISABLED'));
		$options[] = \JHtml::_('select.option', '1', \JText::_('JENABLED'));
		$options[] = \JHtml::_('select.option', '2', \JText::_('JPROTECTED'));
		$options[] = \JHtml::_('select.option', '3', \JText::_('JUNPROTECTED'));

		return $options;
	}

	/**
	 * Get the extra_query of an update site.
	 *
	 * @param   int  $updateSiteId  The update_site_id of the extension.
	 *
	 * @return  array  Array with the prefix, value and sufix of the extra_query of the update site.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getExtraQuery($updateSiteId)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('client_id, element, folder, e.type, extra_query')
			->from('#__update_sites AS s')
			->innerJoin('#__update_sites_extensions AS se ON (se.update_site_id = s.update_site_id)')
			->innerJoin('#__extensions AS e ON (e.extension_id = se.extension_id)')
			->where($db->quoteName('s.update_site_id') . ' = ' . $updateSiteId);
		$db->setQuery($query);
		$element = $db->loadObject();

		if ($element->client_id)
		{
			$path = JPATH_ADMINISTRATOR;
		}
		else
		{
			$path = JPATH_ROOT;
		}

		switch ($element->type)
		{
			case 'component':
				$path .= '/components/' . $element->element . '/' . substr($element->element, 4) . '.xml';
				break;
			case 'plugin':
				$path .= '/plugins/' . $element->folder . '/' . $element->element . '/' . $element->element . '.xml';
				break;
			case 'module':
				$path .= '/modules/' . $element->element . '/' . $element->element . '.xml';
				break;
			case 'template':
				$path .= '/templates/' . $element->element . '/templateDetails.xml';
				break;
		}

		$installXmlFile = simplexml_load_file($path);

		$prefix = $installXmlFile->dlid['prefix'];
		$sufix = $installXmlFile->dlid['sufix'];
		$value = substr($element->extra_query, strlen($prefix));

		if ($sufix != null)
		{
			$value = substr($value, 0, -strlen($sufix));
		}

		$extraQuery = array(
			'prefix'  => $prefix,
			'sufix'   => $sufix,
			'value'   => $value
		);

		return $extraQuery;
	}
}
