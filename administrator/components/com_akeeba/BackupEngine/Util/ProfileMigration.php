<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Exception;

/**
 * This helper class is used to migrate Akeeba Backup profiles to the new storage format implemented since version
 * 6.4.1
 *
 * @since       6.4.1
 */
abstract class ProfileMigration
{
	/**
	 * Tries to migrate a backup profile to the new JSON-based storage format used since version 6.4.1.
	 *
	 * @param   int  $profileID  The ID of the profile to migrate
	 *
	 * @return  bool  Whether we converted the profile
	 *
	 * @since   6.4.1
	 */
	public static function migrateProfile($profileID)
	{
		$platform = Platform::getInstance();
		$db       = Factory::getDatabase($platform->get_platform_database_options());

		// Is the database connected?
		if (!$db->connected())
		{
			return false;
		}

		// Load the raw data from the database
		try
		{
			$sql = $db->getQuery(true)
				->select('*')
				->from($db->qn($platform->tableNameProfiles))
				->where($db->qn('id') . ' = ' . $db->q($profileID));

			$rawData = $db->setQuery($sql)->loadAssoc();
		}
		catch (Exception $e)
		{
			return false;
		}

		// Decrypt the configuration data if required
		$rawData['configuration'] = self::decryptConfiguration($rawData['configuration']);
		$migrated                 = false;

		// Migrate the configuration from INI to JSON format
		if (self::looksLikeIni($rawData['configuration']))
		{
			$rawData['configuration'] = self::convertINItoJSON($rawData['configuration']);

			$migrated = true;
		}

		// Migrate the filters from INI to JSON format
		if (self::looksLikeSerialized($rawData['filters']))
		{
			$rawData['filters'] = self::convertSerializedToJSON($rawData['filters']);

			$migrated = true;
		}

		if (!$migrated)
		{
			return false;
		}

		$rawData['configuration'] = self::encryptConfiguration($rawData['configuration']);

		$sql = $db->getQuery(true)
			->update($db->qn($platform->tableNameProfiles))
			->set($db->qn('configuration') . ' = ' . $db->q($rawData['configuration']))
			->set($db->qn('filters') . ' = ' . $db->q($rawData['filters']))
			->where($db->qn('id') . ' = ' . $db->q($profileID));

		$db->setQuery($sql);

		try
		{
			$result = $db->query();
		}
		catch (Exception $exc)
		{
			return false;
		}

		return ($result == true);
	}

	/**
	 * Decrypt the configuration data if necessary. Returns the decrypted data.
	 *
	 * @param   string  $configData  The possibly encrypted data.
	 *
	 * @return  string  The decrypted data
	 *
	 * @since   6.4.1
	 */
	public static function decryptConfiguration($configData)
	{
		$noData    = empty($configData);
		$signature = ($noData || (strlen($configData) < 12)) ? '' : substr($configData, 0, 12);

		if (in_array($signature, ['###AES128###', '###CTR128###']))
		{
			return Factory::getSecureSettings()->decryptSettings($configData);
		}

		return $configData;
	}

	/**
	 * Encrypt the configuration data if necessary.
	 *
	 * @param   string  $configData  The raw configuration data
	 *
	 * @return  string  The possibly encrypted configuration data
	 *
	 * @since   6.4.1
	 */
	public static function encryptConfiguration($configData)
	{
		$secureSettings = Factory::getSecureSettings();

		return $secureSettings->encryptSettings($configData);
	}

	/**
	 * Does the provided configuration data look like it's INI encoded?
	 *
	 * @param   string  $configData  The unencrypted configuration data we read from the database.
	 *
	 * @return  bool
	 *
	 * @since   6.4.1
	 */
	public static function looksLikeIni($configData)
	{
		if (empty($configData))
		{
			return false;
		}

		if (strlen($configData) < 8)
		{
			return false;
		}

		if ((substr($configData, 0, 8) == '[global]') || substr($configData, 0, 8) == '[akeeba]')
		{
			return true;
		}

		return false;
	}

	/**
	 * Convert the INI-encoded data to JSON-encoded data
	 *
	 * @param   string  $configData  The INI-encoded data
	 *
	 * @return  string  The JSON-encoded data
	 *
	 * @since   6.4.1
	 */
	public static function convertINItoJSON($configData)
	{
		$dataArray = ParseIni::parse_ini_file($configData, true, true);

		return json_encode($dataArray, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	}

	/**
	 * Does the raw filters string provided seems to use serialized data? We actually check if it looks like JSON.
	 * If it's not, we assume it's serialized data.
	 *
	 * @param   string  $rawFilters  The raw filters string
	 *
	 * @return  bool  Does it look like a serialized string?
	 *
	 * @since   6.4.1
	 */
	public static function looksLikeSerialized($rawFilters)
	{
		if (empty($rawFilters))
		{
			return false;
		}

		if (substr($rawFilters, 0, 1) == '{')
		{
			return false;
		}

		return true;
	}

	/**
	 * Convert the serialized array in $rawFilters to JSON representation
	 *
	 * @param   string  $rawFilters  Raw serialized string
	 *
	 * @return  string  JSON-encoded string
	 *
	 * @since   6.4.1
	 */
	public static function convertSerializedToJSON($rawFilters)
	{
		$filters = unserialize($rawFilters);

		if (empty($filters))
		{
			$filters = [];
		}

		return json_encode($filters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
	}
}
