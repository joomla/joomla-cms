<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\String\StringHelper;

/**
 * Finder extension plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgExtensionFinder extends CMSPlugin
{
	/**
	 * Add common words to finder after language got installed
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		if (!$eid)
		{
			return;
		}

		$extension = $this->getLanguage($eid);

		if ($extension)
		{
			$this->removeCommonWords($extension);
			$this->addCommonWords($extension);
		}
	}

	/**
	 * Add common words to finder after language got updated
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension identifier
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate($installer, $eid)
	{
		$this->onExtensionAfterInstall($installer, $eid);
	}

	/**
	 * Remove common words to finder after language got uninstalled
	 *
	 * @param   JInstaller  $installer  Installer instance
	 * @param   integer     $eid        Extension id
	 * @param   boolean     $removed    Installation result
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionBeforeUninstall($installer, $eid, $removed)
	{
		$extension = $this->getLanguage($eid);

		if ($extension)
		{
			$this->removeCommonWords($extension);
		}
	}

	/**
	 * Get an object of information if the handled extension is a language
	 * 
	 * @param   integer  $eid  Extensuon id
	 * 
	 * @return  object;
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLanguage($eid)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('element, client_id')
			->from('#__extensions')
			->where('extension_id = ' . (int) $eid)
			->where('type = ' . $db->quote('language'));
		$db->setQuery($query);
		$extension = $db->loadObject();

		return $extension;
	}

	/**
	 * Add common words from a txt file to com_finder
	 * 
	 * @param   object  $extension  Extension object
	 * 
	 * @return  void
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addCommonWords($extension)
	{
		if ($extension->client_id == 0)
		{
			$path = JPATH_SITE . '/language/' . $extension->element . '/' . $extension->element . '.com_finder.commonwords.txt';
		}
		else
		{
			$path = JPATH_ADMINISTRATOR . '/language/' . $extension->element . '/' . $extension->element . '.com_finder.commonwords.txt';
		}

		if (!file_exists($path))
		{
			return;
		}

		$file_content = file_get_contents($path);
		$words = explode("\n", $file_content);
		$words = array_map(function ($word)
			{
				// Remove comments
				if (StringHelper::strpos($word, ';') !== false)
				{
					$word = StringHelper::substr($word, 0, StringHelper::strpos($word, ';'));
				}

				return $word;
			}
			, $words);

		$words = array_filter(array_map('trim', $words));
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php';
		$lang = \FinderIndexerHelper::getPrimaryLanguage($extension->element);
		$query->insert('#__finder_terms_common')
			->columns(array($db->qn('term'), $db->qn('language'), $db->qn('custom')));
		$template = ',' . $db->q($lang) . ',0';

		foreach ($words as $word)
		{
			$query->values($db->q($word) . $template);
		}

		try
		{
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $ex)
		{
			// It would be nice if the common word is stored to the DB, but it isn't super important
		}
	}

	/**
	 * Remove common words of a language from com_finder
	 * 
	 * @param   object  $extension  Extension object
	 * 
	 * @return  void
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function removeCommonWords($extension)
	{
		$db = Factory::getDbo();
		require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php';
		$lang = \FinderIndexerHelper::getPrimaryLanguage($extension->element);
		$query = $db->getQuery(true)
			->delete('#__finder_terms_common')
			->where('language = ' . $db->quote($lang))
			->where('custom = 0');
		$db->setQuery($query);
		$db->execute();
	}
}
