<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Language helper class
 *
 * @since  11.1
 */
class JLanguageHelper
{
	/**
	 * Builds a list of the system languages which can be used in a select option
	 *
	 * @param   string   $actualLanguage  Client key for the area
	 * @param   string   $basePath        Base path to use
	 * @param   boolean  $caching         True if caching is used
	 * @param   boolean  $installed       Get only installed languages
	 *
	 * @return  array  List of system languages
	 *
	 * @since   11.1
	 */
	public static function createLanguageList($actualLanguage, $basePath = JPATH_BASE, $caching = false, $installed = false)
	{
		$list      = array();
		$clientId  = $basePath === JPATH_ADMINISTRATOR ? 1 : 0;
		$languages = $installed ? static::getInstalledLanguages($clientId, true) : self::getKnownLanguages($basePath);

		foreach ($languages as $languageCode => $language)
		{
			$metadata = $installed ? $language->metadata : $language;

			$list[] = array(
				'text'     => isset($metadata['nativeName']) ? $metadata['nativeName'] : $metadata['name'],
				'value'    => $languageCode,
				'selected' => $languageCode === $actualLanguage ? 'selected="selected"' : null,
			);
		}

		return $list;
	}

	/**
	 * Tries to detect the language.
	 *
	 * @return  string  locale or null if not found
	 *
	 * @since   11.1
	 */
	public static function detectLanguage()
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$systemLangs = self::getLanguages();

			foreach ($browserLangs as $browserLang)
			{
				// Slice out the part before ; on first step, the part before - on second, place into array
				$browserLang = substr($browserLang, 0, strcspn($browserLang, ';'));
				$primary_browserLang = substr($browserLang, 0, 2);

				foreach ($systemLangs as $systemLang)
				{
					// Take off 3 letters iso code languages as they can't match browsers' languages and default them to en
					$Jinstall_lang = $systemLang->lang_code;

					if (strlen($Jinstall_lang) < 6)
					{
						if (strtolower($browserLang) == strtolower(substr($systemLang->lang_code, 0, strlen($browserLang))))
						{
							return $systemLang->lang_code;
						}
						elseif ($primary_browserLang == substr($systemLang->lang_code, 0, 2))
						{
							$primaryDetectedLang = $systemLang->lang_code;
						}
					}
				}

				if (isset($primaryDetectedLang))
				{
					return $primaryDetectedLang;
				}
			}
		}

		return;
	}

	/**
	 * Get available languages
	 *
	 * @param   string  $key  Array key
	 *
	 * @return  array  An array of published languages
	 *
	 * @since   11.1
	 */
	public static function getLanguages($key = 'default')
	{
		static $languages;

		if (empty($languages))
		{
			// Installation uses available languages
			if (JFactory::getApplication()->getClientId() == 2)
			{
				$languages[$key] = array();
				$knownLangs = self::getKnownLanguages(JPATH_BASE);

				foreach ($knownLangs as $metadata)
				{
					// Take off 3 letters iso code languages as they can't match browsers' languages and default them to en
					$obj = new stdClass;
					$obj->lang_code = $metadata['tag'];
					$languages[$key][] = $obj;
				}
			}
			else
			{
				$cache = JFactory::getCache('com_languages', '');

				if ($cache->contains('languages'))
				{
					$languages = $cache->get('languages');
				}
				else
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select('*')
						->from('#__languages')
						->where('published=1')
						->order('ordering ASC');
					$db->setQuery($query);

					$languages['default'] = $db->loadObjectList();
					$languages['sef'] = array();
					$languages['lang_code'] = array();

					if (isset($languages['default'][0]))
					{
						foreach ($languages['default'] as $lang)
						{
							$languages['sef'][$lang->sef] = $lang;
							$languages['lang_code'][$lang->lang_code] = $lang;
						}
					}

					$cache->store($languages, 'languages');
				}
			}
		}

		return $languages[$key];
	}

	/**
	 * Get a list of installed languages.
	 *
	 * @param   integer  $clientId         The client app id.
	 * @param   boolean  $processMetaData  Fetch Language metadata.
	 * @param   boolean  $processManifest  Fetch Language manifest.
	 * @param   string   $pivot            The pivot of the returning array.
	 * @param   string   $orderField       Field to order the results.
	 * @param   string   $orderDirection   Direction to order the results.
	 *
	 * @return  array  Array with the installed languages.
	 *
	 * @since   3.7.0
	 */
	public static function getInstalledLanguages($clientId = null, $processMetaData = false, $processManifest = false, $pivot = 'element',
		$orderField = null, $orderDirection = null)
	{
		static $installedLanguages = null;

		if ($installedLanguages === null)
		{
			$cache = JFactory::getCache('com_languages', '');

			if ($cache->contains('installedlanguages'))
			{
				$installedLanguages = $cache->get('installedlanguages');
			}
			else
			{
				$db = JFactory::getDbo();

				$query = $db->getQuery(true)
					->select($db->quoteName(array('element', 'name', 'client_id', 'extension_id')))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('language'))
					->where($db->quoteName('state') . ' = 0')
					->where($db->quoteName('enabled') . ' = 1');

				$installedLanguages = $db->setQuery($query)->loadObjectList();

				$cache->store($installedLanguages, 'installedlanguages');
			}
		}

		$clients   = $clientId === null ? array(0, 1) : array((int) $clientId);
		$languages = array(
			0 => array(),
			1 => array(),
		);

		foreach ($installedLanguages as $language)
		{
			// If the language client is not needed continue cycle. Drop for performance.
			if (!in_array((int) $language->client_id, $clients))
			{
				continue;
			}

			$lang = $language;

			if ($processMetaData || $processManifest)
			{
				$clientPath = (int) $language->client_id === 0 ? JPATH_SITE : JPATH_ADMINISTRATOR;
				$metafile   = self::getLanguagePath($clientPath, $language->element) . '/' . $language->element . '.xml';

				// Process the language metadata.
				if ($processMetaData)
				{
					try
					{
						$lang->metadata = self::parseXMLLanguageFile($metafile);
					}
					// Not able to process xml language file. Fail silently.
					catch (Exception $e)
					{
						JLog::add(JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METAFILE', $language->element, $metafile), JLog::WARNING, 'language');

						continue;
					}

					// No metadata found, not a valid language. Fail silently.
					if (!is_array($lang->metadata))
					{
						JLog::add(JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METADATA', $language->element, $metafile), JLog::WARNING, 'language');

						continue;
					}
				}

				// Process the language manifest.
				if ($processManifest)
				{
					try
					{
						$lang->manifest = JInstaller::parseXMLInstallFile($metafile);
					}
					// Not able to process xml language file. Fail silently.
					catch (Exception $e)
					{
						JLog::add(JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METAFILE', $language->element, $metafile), JLog::WARNING, 'language');

						continue;
					}

					// No metadata found, not a valid language. Fail silently.
					if (!is_array($lang->manifest))
					{
						JLog::add(JText::sprintf('JLIB_LANGUAGE_ERROR_CANNOT_LOAD_METADATA', $language->element, $metafile), JLog::WARNING, 'language');

						continue;
					}
				}
			}

			$languages[$language->client_id][] = $lang;
		}

		// Order the list, if needed.
		if ($orderField !== null && $orderDirection !== null)
		{
			$orderDirection = strtolower($orderDirection) === 'desc' ? -1 : 1;

			foreach ($languages as $cId => $language)
			{
				// If the language client is not needed continue cycle. Drop for performance.
				if (!in_array($cId, $clients))
				{
					continue;
				}

				$languages[$cId] = ArrayHelper::sortObjects($languages[$cId], $orderField, $orderDirection, true, true);
			}
		}

		// Add the pivot, if needed.
		if ($pivot !== null)
		{
			foreach ($languages as $cId => $language)
			{
				// If the language client is not needed continue cycle. Drop for performance.
				if (!in_array($cId, $clients))
				{
					continue;
				}

				$languages[$cId] = ArrayHelper::pivot($languages[$cId], $pivot);
			}
		}

		return $clientId !== null ? $languages[$clientId] : $languages;
	}

	/**
	 * Get a list of content languages.
	 *
	 * @param   boolean  $checkPublished  Check if the content language is published.
	 * @param   boolean  $checkInstalled  Check if the content language is installed.
	 * @param   string   $pivot           The pivot of the returning array.
	 * @param   string   $orderField      Field to order the results.
	 * @param   string   $orderDirection  Direction to order the results.
	 *
	 * @return  array  Array of the content languages.
	 *
	 * @since   3.7.0
	 */
	public static function getContentLanguages($checkPublished = true, $checkInstalled = true, $pivot = 'lang_code', $orderField = null,
		$orderDirection = null)
	{
		static $contentLanguages = null;

		if ($contentLanguages === null)
		{
			$cache = JFactory::getCache('com_languages', '');

			if ($cache->contains('contentlanguages'))
			{
				$contentLanguages = $cache->get('contentlanguages');
			}
			else
			{
				$db = JFactory::getDbo();

				$query = $db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__languages'));

				$contentLanguages = $db->setQuery($query)->loadObjectList();

				$cache->store($contentLanguages, 'contentlanguages');
			}
		}

		$languages = $contentLanguages;

		// Check if the language is published, if needed.
		if ($checkPublished)
		{
			foreach ($languages as $key => $language)
			{
				if ((int) $language->published === 0)
				{
					unset($languages[$key]);
				}
			}
		}

		// Check if the language is installed, if needed.
		if ($checkInstalled)
		{
			$languages = array_values(array_intersect_key(ArrayHelper::pivot($languages, 'lang_code'), static::getInstalledLanguages(0)));
		}

		// Order the list, if needed.
		if ($orderField !== null && $orderDirection !== null)
		{
			$languages = ArrayHelper::sortObjects($languages, $orderField, strtolower($orderDirection) === 'desc' ? -1 : 1, true, true);
		}

		// Add the pivot, if needed.
		if ($pivot !== null)
		{
			$languages = ArrayHelper::pivot($languages, $pivot);
		}

		return $languages;
	}

	/**
	 * Parse strings from a language file.
	 *
	 * @param   string   $fileName  The language ini file path.
	 * @param   boolean  $debug     If set to true debug language ini file.
	 *
	 * @return  boolean  True if saved, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function parseIniFile($fileName, $debug = false)
	{
		// Check if file exists.
		if (!file_exists($fileName))
		{
			return array();
		}

		// @deprecated __DEPLOY_VERSION__ Usage of "_QQ_" is deprecated. Use escaped double quotes (\") instead.
		if (!defined('_QQ_'))
		{
			define('_QQ_', '"');
		}

		// Capture hidden PHP errors from the parsing.
		if ($debug === true)
		{
			// See https://secure.php.net/manual/en/reserved.variables.phperrormsg.php
			$php_errormsg = null;

			$trackErrors = ini_get('track_errors');
			ini_set('track_errors', true);
		}

		$strings = @parse_ini_file($fileName);

		// Restore error tracking to what it was before.
		if ($debug === true)
		{
			ini_set('track_errors', $trackErrors);
		}

		return is_array($strings) ? $strings : array();
	}

	/**
	 * Save strings to a language file.
	 *
	 * @param   string  $fileName  The language ini file path.
	 * @param   array   $strings   The array of strings.
	 *
	 * @return  boolean  True if saved, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function saveToIniFile($fileName, array $strings)
	{
		JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');

		// Escape double quotes.
		foreach ($strings as $key => $string)
		{
			$strings[$key] = addcslashes($string, '"');
		}

		// Write override.ini file with the strings.
		$registry = new Joomla\Registry\Registry($strings);

		return JFile::write($fileName, $registry->toString('INI'));
	}

	/**
	 * Checks if a language exists.
	 *
	 * This is a simple, quick check for the directory that should contain language files for the given user.
	 *
	 * @param   string  $lang      Language to check.
	 * @param   string  $basePath  Optional path to check.
	 *
	 * @return  boolean  True if the language exists.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function exists($lang, $basePath = JPATH_BASE)
	{
		static $paths = array();

		// Return false if no language was specified
		if (!$lang)
		{
			return false;
		}

		$path = $basePath . '/language/' . $lang;

		// Return previous check results if it exists
		if (isset($paths[$path]))
		{
			return $paths[$path];
		}

		// Check if the language exists
		$paths[$path] = is_dir($path);

		return $paths[$path];
	}

	/**
	 * Returns an associative array holding the metadata.
	 *
	 * @param   string  $lang  The name of the language.
	 *
	 * @return  mixed  If $lang exists return key/value pair with the language metadata, otherwise return NULL.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getMetadata($lang)
	{
		$file   = self::getLanguagePath(JPATH_BASE, $lang) . '/' . $lang . '.xml';
		$result = null;

		if (is_file($file))
		{
			$result = self::parseXMLLanguageFile($file);
		}

		if (empty($result))
		{
			return;
		}

		return $result;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @param   string  $basePath  The basepath to use
	 *
	 * @return  array  key/value pair with the language file and real name.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		return self::parseLanguageFiles(self::getLanguagePath($basePath));
	}

	/**
	 * Get the path to a language
	 *
	 * @param   string  $basePath  The basepath to use.
	 * @param   string  $language  The language tag.
	 *
	 * @return  string  language related path or null.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		return $basePath . '/language' . (!empty($language) ? '/' . $language : '');
	}

	/**
	 * Searches for language directories within a certain base dir.
	 *
	 * @param   string  $dir  directory of files.
	 *
	 * @return  array  Array holding the found languages as filename => real name pairs.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function parseLanguageFiles($dir = null)
	{
		$languages = array();

		// Search main language directory for subdirectories
		foreach (glob($dir . '/*', GLOB_NOSORT | GLOB_ONLYDIR) as $directory)
		{
			// But only directories with lang code format
			if (preg_match('#/[a-z]{2,3}-[A-Z]{2}$#', $directory))
			{
				$dirPathParts = pathinfo($directory);
				$file         = $directory . '/' . $dirPathParts['filename'] . '.xml';

				if (!is_file($file))
				{
					continue;
				}

				try
				{
					// Get installed language metadata from xml file and merge it with lang array
					if ($metadata = self::parseXMLLanguageFile($file))
					{
						$languages = array_replace($languages, array($dirPathParts['filename'] => $metadata));
					}
				}
				catch (RuntimeException $e)
				{
				}
			}
		}

		return $languages;
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param   string  $path  Path to the XML files.
	 *
	 * @return  array  Array holding the found metadata as a key => value pair.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  RuntimeException
	 */
	public static function parseXMLLanguageFile($path)
	{
		if (!is_readable($path))
		{
			throw new RuntimeException('File not found or not readable');
		}

		// Try to load the file
		$xml = simplexml_load_file($path);

		if (!$xml)
		{
			return;
		}

		// Check that it's a metadata file
		if ((string) $xml->getName() != 'metafile')
		{
			return;
		}

		$metadata = array();

		foreach ($xml->metadata->children() as $child)
		{
			$metadata[$child->getName()] = (string) $child;
		}

		return $metadata;
	}
}
