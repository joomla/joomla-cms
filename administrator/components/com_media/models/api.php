<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Model\Model;

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaModelApi extends Model
{
	/**
	 * Holds avaliable media file adapters
	 *
	 * @var   MediaFileAdapterInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $adapters = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (!isset($config['providers']))
		{
			$config['providers'] = Joomla\CMS\Plugin\PluginHelper::getPlugin('filesystem');
		}

		$providers = $config['providers'];

		if (!isset($config['fileadapters']))
		{
			// Import enabled file system plugins
			Joomla\CMS\Plugin\PluginHelper::importPlugin('filesystem');

			$app = JFactory::getApplication();

			$results = $app->triggerEvent('onFileSystemGetAdapters');
			$adapters = array();

			for ($i = 0, $len = count($results); $i < $len; $i++)
			{
				$adapters[$providers[$i]->name] = $results[$i];
			}

			$config['fileadapters'] = $adapters;
		}

		$this->adapters = $config['fileadapters'];
	}

	/**
	 * Return the requested adapter
	 *
	 * @param   string  $name  Name of the adapter
	 *
	 * @since   __DEPLOY_VERSION__
	 * @return MediaFileAdapterInterface
	 *
	 * @throws Exception
	 */
	private function getAdapter($name)
	{
		if (isset($this->adapters[$name]))
		{
			return $this->adapters[$name];
		}

		// Todo Use a translated string
		throw new InvalidArgumentException('Requested media file adapter was not found', 500);
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in MediaFileAdapterInterface::getFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to the file or folder
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFile($adapter, $path = '/')
	{
		// Add adapter info to the file returned
		$file = $this->getAdapter($adapter)->getFile($path);
		$file->path = $adapter . ":" . $file->path;

		return $file;
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in MediaFileAdapterInterface::getFiles().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The folder
	 * @param   string  $filter   The filter
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFiles($adapter, $path = '/', $filter = '')
	{
		// Add adapter info to all the files to be returned
		$files = $this->getAdapter($adapter)->getFiles($path, $filter);

		foreach ($files as $file)
		{
			$file->path = $adapter . ":" . $file->path;
		}

		return $files;
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in MediaFileAdapterInterface::createFolder().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFolder()
	 */
	public function createFolder($adapter, $name, $path)
	{
		$name = $this->getSafeName($name);

		$this->getAdapter($adapter)->createFolder($name, $path);

		return $name;
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::createFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 * @param   binary  $data     The data
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFile()
	 */
	public function createFile($adapter, $name, $path, $data)
	{
		$name = $this->getSafeName($name);

		$this->getAdapter($adapter)->createFile($name, $path, $data);

		return $name;
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::updateFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 * @param   binary  $data     The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::updateFile()
	 */
	public function updateFile($adapter, $name, $path, $data)
	{
		$this->getAdapter($adapter)->updateFile($name, $path, $data);
	}

	/**
	 * Deletes the folder or file of the given path. More information
	 * can be found in MediaFileAdapterInterface::delete().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::delete()
	 */
	public function delete($adapter, $path)
	{
		$this->getAdapter($adapter)->delete($path);
	}

	/**
	 * Creates a safe file name for the given name.
	 *
	 * @param   string  $name  The filename
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	private function getSafeName($name)
	{
		// Make the filename safe
		$name = JFile::makeSafe($name);

		// Transform filename to punycode
		$name = JStringPunycode::toPunycode($name);

		$extension = JFile::getExt($name);

		if ($extension)
		{
			$extension = '.' . strtolower($extension);
		}

		// Transform filename to punycode, then neglect other than non-alphanumeric characters & underscores.
		// Also transform extension to lowercase.
		$nameWithoutExtension = substr($name, 0, strlen($name) - strlen($extension));
		$name = preg_replace(array("/[\\s]/", '/[^a-zA-Z0-9_]/'), array('_', ''), $nameWithoutExtension) . $extension;

		return $name;
	}

	/**
	 * Copies file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $adapter          The adapter
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function copy($adapter, $sourcePath, $destinationPath, $force = false)
	{
		$this->getAdapter($adapter)->copy($sourcePath, $destinationPath, $force);
	}

	/**
	 * Moves file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $adapter          The adapter
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function move($adapter, $sourcePath, $destinationPath, $force = false)
	{
		$this->getAdapter($adapter)->move($sourcePath, $destinationPath, $force);
	}
}
