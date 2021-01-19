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

/**
 * Temporary files management class. Handles creation, tracking and cleanup.
 */
class TemporaryFiles
{

	/**
	 * Creates a randomly-named temporary file, registers it with the temporary
	 * files management and returns its absolute path
	 *
	 * @return  string  The temporary file name
	 */
	public function createRegisterTempFile()
	{
		// Create a randomly named file in the temp directory
		$registry = Factory::getConfiguration();
		$tempFile = tempnam($registry->get('akeeba.basic.output_directory'), 'ak');

		// Register it and return its absolute path
		$tempName = basename($tempFile);

		return Factory::getTempFiles()->registerTempFile($tempName);
	}

	/**
	 * Registers a temporary file with the Akeeba Engine, storing the list of temporary files
	 * in another temporary flat database file.
	 *
	 * @param   string  $fileName  The path of the file, relative to the temporary directory
	 *
	 * @return  string  The absolute path to the temporary file, for use in file operations
	 */
	public function registerTempFile($fileName)
	{
		$configuration = Factory::getConfiguration();
		$tempFiles     = $configuration->get('volatile.tempfiles', false);
		if ($tempFiles === false)
		{
			$tempFiles = [];
		}
		else
		{
			$tempFiles = @unserialize($tempFiles);

			if ($tempFiles === false)
			{
				$tempFiles = [];
			}
		}

		if (!in_array($fileName, $tempFiles))
		{
			$tempFiles[] = $fileName;
			$configuration->set('volatile.tempfiles', serialize($tempFiles));
		}

		return Factory::getFilesystemTools()->TranslateWinPath($configuration->get('akeeba.basic.output_directory') . '/' . $fileName);
	}

	/**
	 * Unregister and delete a temporary file
	 *
	 * @param   string  $fileName      The filename to unregister and delete
	 * @param   bool    $removePrefix  The prefix to remove
	 *
	 * @return  bool  True on success
	 */
	public function unregisterAndDeleteTempFile($fileName, $removePrefix = false)
	{
		$configuration = Factory::getConfiguration();

		if ($removePrefix)
		{
			$fileName = str_replace(Factory::getFilesystemTools()->TranslateWinPath($configuration->get('akeeba.basic.output_directory')), '', $fileName);

			if ((substr($fileName, 0, 1) == '/') || (substr($fileName, 0, 1) == '\\'))
			{
				$fileName = substr($fileName, 1);
			}

			if ((substr($fileName, -1) == '/') || (substr($fileName, -1) == '\\'))
			{
				$fileName = substr($fileName, 0, -1);
			}
		}

		// Make sure this file is registered
		$configuration = Factory::getConfiguration();

		$serialised = $configuration->get('volatile.tempfiles', false);
		$tempFiles  = [];

		if ($serialised !== false)
		{
			$tempFiles = @unserialize($serialised);
		}

		if (!is_array($tempFiles))
		{
			return false;
		}

		if (!in_array($fileName, $tempFiles))
		{
			return false;
		}

		$file = $configuration->get('akeeba.basic.output_directory') . '/' . $fileName;
		Factory::getLog()->debug("-- Removing temporary file $fileName");
		$platform = strtoupper(PHP_OS);

		// Chown normally doesn't work on Windows but many years ago I found it necessary to delete temp files. No idea.
		if ((substr($platform, 0, 6) == 'CYGWIN') || (substr($platform, 0, 3) == 'WIN'))
		{
			// On Windows we have to chown() the file first to make it owned by Nobody
			Factory::getLog()->debug("-- Windows hack: chowning $fileName");
			@chown($file, 600);
		}

		$result = @$this->nullifyAndDelete($file);

		// Make sure the file is removed before unregistering it
		if (!@file_exists($file))
		{
			$aPos = array_search($fileName, $tempFiles);

			if ($aPos !== false)
			{
				unset($tempFiles[$aPos]);

				$configuration->set('volatile.tempfiles', serialize($tempFiles));
			}
		}

		return $result;
	}


	/**
	 * Deletes all temporary files
	 *
	 * @return  void
	 */
	public function deleteTempFiles()
	{
		$configuration = Factory::getConfiguration();

		$serialised = $configuration->get('volatile.tempfiles', false);
		$tempFiles  = [];

		if ($serialised !== false)
		{
			$tempFiles = @unserialize($serialised);
		}

		if (!is_array($tempFiles))
		{
			$tempFiles = [];
		}

		$fileName = null;

		if (!empty($tempFiles))
		{
			foreach ($tempFiles as $fileName)
			{
				Factory::getLog()->debug("-- Removing temporary file $fileName");
				$file     = $configuration->get('akeeba.basic.output_directory') . '/' . $fileName;
				$platform = strtoupper(PHP_OS);

				// Chown normally doesn't work on Windows but many years ago I found it necessary to delete temp files. No idea.
				if ((substr($platform, 0, 6) == 'CYGWIN') || (substr($platform, 0, 3) == 'WIN'))
				{
					// On Windows we have to chwon() the file first to make it owned by Nobody
					@chown($file, 600);
				}

				$ret = @$this->nullifyAndDelete($file);
			}
		}

		$tempFiles = [];
		$configuration->set('volatile.tempfiles', serialize($tempFiles));
	}

	/**
	 * Nullify the contents of the file and try to delete it as well
	 *
	 * @param   string  $filename  The absolute path to the file to delete
	 *
	 * @return  bool  True of the deletion is successful
	 */
	public function nullifyAndDelete($filename)
	{
		// Try to nullify (method #1)
		$fp = @fopen($filename, 'w');

		if (is_resource($fp))
		{
			@fclose($fp);
		}
		else
		{
			// Try to nullify (method #2)
			@file_put_contents($filename, '');
		}

		// Unlink
		return @unlink($filename);
	}
}
