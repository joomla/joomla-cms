<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.Finder
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Finder extension plugin
 *
 * @since  4.0.0
 */
class PlgExtensionFinder extends CMSPlugin
{
	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * Add common words to finder after language got installed
	 *
	 * @param   JInstaller  $installer  Installer object
	 * @param   integer     $eid        Extension Identifier
	 *
	 * @return  void
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
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
	 * @since   4.0.0
	 */
	public function onExtensionAfterUninstall($installer, $eid, $removed)
	{
		// Check that the language was successfully uninstalled.
		if ($eid && $removed && $installer->extension->type === 'language')
		{
			$this->removeCommonWords($installer->extension);
		}
	}

	/**
	 * Get an object of information if the handled extension is a language
	 *
	 * @param   integer  $eid  Extension id
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function getLanguage($eid)
	{
		$db  = $this->db;
		$eid = (int) $eid;

		$query = $db->getQuery(true)
			->select($db->quoteName(['element', 'client_id']))
			->from($db->quoteName('#__extensions'))
			->where(
				[
					$db->quoteName('extension_id') . ' = :eid',
					$db->quoteName('type') . ' = ' . $db->quote('language'),
				]
			)
			->bind(':eid', $eid, ParameterType::INTEGER);

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
	 * @since   4.0.0
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
		$words = array_map(
			function ($word)
			{
				// Remove comments
				if (StringHelper::strpos($word, ';') !== false)
				{
					$word = StringHelper::substr($word, 0, StringHelper::strpos($word, ';'));
				}

				return $word;
			}, $words
		);

		$words = array_filter(array_map('trim', $words));
		$db    = $this->db;
		$query = $db->getQuery(true);

		require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php';

		$lang = Helper::getPrimaryLanguage($extension->element);

		$query->insert($db->quoteName('#__finder_terms_common'))
			->columns($db->quoteName(['term', 'language', 'custom']));

		foreach ($words as $word)
		{
			$bindNames = $query->bindArray([$word, $lang], ParameterType::STRING);

			$query->values(implode(',', $bindNames) . ', 0');
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
	 * @since   4.0.0
	 */
	protected function removeCommonWords($extension)
	{
		$db = $this->db;

		require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/helper.php';

		$lang = Helper::getPrimaryLanguage($extension->element);

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__finder_terms_common'))
			->where(
				[
					$db->quoteName('language') . ' = :lang',
					$db->quoteName('custom') . ' = 0',
				]
			)
			->bind(':lang', $lang);

		$db->setQuery($query);
		$db->execute();
	}
}
