<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Privacy component helper.
 *
 * @since  3.9.0
 */
class PrivacyHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_DASHBOARD'),
			'index.php?option=com_privacy&view=dashboard',
			$vName === 'dashboard'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_REQUESTS'),
			'index.php?option=com_privacy&view=requests',
			$vName === 'requests'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_CAPABILITIES'),
			'index.php?option=com_privacy&view=capabilities',
			$vName === 'capabilities'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_PRIVACY_SUBMENU_CONSENTS'),
			'index.php?option=com_privacy&view=consents',
			$vName === 'consents'
		);
	}

	/**
	 * Render the data request as a XML document.
	 *
	 * @param   PrivacyExportDomain[]  $exportData  The data to be exported.
	 *
	 * @return  string
	 *
	 * @since   3.9.0
	 */
	public static function renderDataAsXml(array $exportData)
	{
		$export = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><data-export />');

		foreach ($exportData as $domain)
		{
			$xmlDomain = $export->addChild('domain');
			$xmlDomain->addAttribute('name', $domain->name);
			$xmlDomain->addAttribute('description', $domain->description);

			foreach ($domain->getItems() as $item)
			{
				$xmlItem = $xmlDomain->addChild('item');

				if ($item->id)
				{
					$xmlItem->addAttribute('id', $item->id);
				}

				foreach ($item->getFields() as $field)
				{
					$xmlItem->{$field->name} = $field->value;
				}
			}
		}

		$dom = new DOMDocument;
		$dom->loadXML($export->asXML());
		$dom->formatOutput = true;

		return $dom->saveXML();
	}

	/**
	 * Gets the privacyconsent system plugin extension id.
	 *
	 * @return  integer  The privacyconsent system plugin extension id.
	 *
	 * @since   3.9.2
	 */
	public static function getPrivacyConsentPluginId()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'));

		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		return $result;
	}
}
