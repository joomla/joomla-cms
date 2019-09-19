<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

/**
 * Installer helper.
 *
 * @since  1.6
 */
class InstallerHelper
{
	/**
	 * Get a list of filter options for the extension types.
	 *
	 * @return  array  An array of \stdClass objects.
	 *
	 * @since   3.0
	 */
	public static function getExtensionTypes()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT type')
			->from('#__extensions');
		$db->setQuery($query);
		$types = $db->loadColumn();

		$options = array();

		foreach ($types as $type)
		{
			$options[] = HTMLHelper::_('select.option', $type, Text::_('COM_INSTALLER_TYPE_' . strtoupper($type)));
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
	public static function getExtensionGroups()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT ' . $db->quoteName('folder'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' != ' . $db->quote(''))
			->order($db->quoteName('folder'));
		$db->setQuery($query);
		$folders = $db->loadColumn();

		$options = array();

		foreach ($folders as $folder)
		{
			$options[] = HTMLHelper::_('select.option', $folder, $folder);
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
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JSITE'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('JADMINISTRATOR'));

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
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JDISABLED'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('JENABLED'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('JPROTECTED'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('JUNPROTECTED'));

		return $options;
	}

	/**
	 * Get a list of filter options for the application statuses.
	 *
	 * @param   string   $element    element of an extension
	 * @param   string   $type       type of an extension
	 * @param   integer  $client_id  client_id of an extension
	 * @param   string   $folder     folder of an extension
	 *
	 * @return  \SimpleXMLElement
	 *
	 * @since   4.0.0
	 */
	public static function getInstallationXML($element, $type, $client_id = 1, $folder = null)
	{
		$path = $client_id ? JPATH_ADMINISTRATOR : JPATH_ROOT;

		switch ($type)
		{
			case 'component':
				$path .= '/components/' . $element . '/' . substr($element, 4) . '.xml';
				break;
			case 'plugin':
				$path .= '/plugins/' . $folder . '/' . $element . '/' . $element . '.xml';
				break;
			case 'module':
				$path .= '/modules/' . $element . '/' . $element . '.xml';
				break;
			case 'template':
				$path .= '/templates/' . $element . '/templateDetails.xml';
				break;
			case 'library':
				$path = JPATH_ADMINISTRATOR . '/manifests/libraries/' . $element . '.xml';
				break;
			case 'file':
				$path = JPATH_ADMINISTRATOR . '/manifests/files/' . $element . '.xml';
				break;
			case 'package':
				$path = JPATH_ADMINISTRATOR . '/manifests/packages/' . $element . '.xml';
		}

		return simplexml_load_file($path);
	}

	/**
	 * Get the download key of an extension going through their installation xml
	 *
	 * @param   CMSObject  $extension  element of an extension
	 *
	 * @return  array  An array with the prefix, suffix and value of the download key
	 *
	 * @since   4.0.0
	 */
	public static function getDownloadKey(CMSObject $extension): array
	{
		$installXmlFile = self::getInstallationXML(
			$extension->get('element'),
			$extension->get('type'),
			$extension->get('client_id'),
			$extension->get('folder')
		);

		if (!$installXmlFile)
		{
			return ['valid' => false];
		}

		if (!isset($installXmlFile->dlid))
		{
			return ['valid' => false];
		}

		$prefix = (string) $installXmlFile->dlid['prefix'];
		$suffix = (string) $installXmlFile->dlid['suffix'];
		$value  = substr($extension->get('extra_query'), strlen($prefix));

		if ($suffix)
		{
			$value = substr($value, 0, -strlen($suffix));
		}

		$downloadKey = [
			'valid'   => $value ? true : false,
			'prefix'  => $prefix,
			'suffix'  => $suffix,
			'value'   => $value
		];

		return $downloadKey;
	}
}
