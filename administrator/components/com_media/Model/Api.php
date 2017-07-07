<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\Model;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Adapter\FileNotFoundException;

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class Api extends Model
{
	/**
	 * Holds avaliable media file adapters
	 *
	 * @var   AdapterInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $adapters = null;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MvcFactoryInterface  $factory  The factory.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		if (!isset($config['providers']))
		{
			$config['providers'] = PluginHelper::getPlugin('filesystem');
		}

		$providers = $config['providers'];

		if (!isset($config['fileadapters']))
		{
			// Import enabled file system plugins
			PluginHelper::importPlugin('filesystem');

			$results = \JFactory::getApplication()->triggerEvent('onFileSystemGetAdapters');
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
	 * @return AdapterInterface
	 *
	 * @throws \Exception
	 */
	private function getAdapter($name)
	{
		if (isset($this->adapters[$name]))
		{
			return $this->adapters[$name];
		}

		// Todo Use a translated string
		throw new \InvalidArgumentException('Requested media file adapter was not found', 500);
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in AdapterInterface::getFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to the file or folder
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFile($adapter, $path = '/')
	{
		// Add adapter prefix to the file returned
		$file = $this->getAdapter($adapter)->getFile($path);
		$file->path = $adapter . ":" . $file->path;

		return $file;
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in AdapterInterface::getFiles().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The folder
	 * @param   string  $filter   The filter
	 * @param   array   $options  The options
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFiles($adapter, $path = '/', $filter = '', $options = array())
	{
		// Add adapter prefix to all the files to be returned
		$files = $this->getAdapter($adapter)->getFiles($path, $filter);

		foreach ($files as $file)
		{
			// If requested add options
			// Url is only can be provided for a file
			if (isset($options['url']) && $options['url'] && $file->type == 'file')
			{
				$file->url = $this->getUrl($adapter, $file->path);
			}

			$file->path = $adapter . ":" . $file->path;
		}

		return $files;
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in AdapterInterface::createFolder().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::createFolder()
	 */
	public function createFolder($adapter, $name, $path)
	{
		$this->getAdapter($adapter)->createFolder($name, $path);

		return $name;
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::createFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 * @param   binary  $data     The data
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::createFile()
	 */
	public function createFile($adapter, $name, $path, $data)
	{
		$this->getAdapter($adapter)->createFile($name, $path, $data);

		return $name;
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::updateFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 * @param   binary  $data     The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::updateFile()
	 */
	public function updateFile($adapter, $name, $path, $data)
	{
		$this->getAdapter($adapter)->updateFile($name, $path, $data);
	}

	/**
	 * Deletes the folder or file of the given path. More information
	 * can be found in AdapterInterface::delete().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::delete()
	 */
	public function delete($adapter, $path)
	{
		$this->getAdapter($adapter)->delete($path);
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
	 * @throws  \Exception
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
	 * @throws  \Exception
	 */
	public function move($adapter, $sourcePath, $destinationPath, $force = false)
	{
		$this->getAdapter($adapter)->move($sourcePath, $destinationPath, $force);
	}

	/**
	 * Returns an url for serve media files from adapter.
	 * Url must provide a valid image type to be displayed on Joomla! site.
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The relative path for the file
	 *
	 * @return string  Permalink to the relative file
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws FileNotFoundException
	 */
	public function getUrl($adapter, $path)
	{
		return $this->getAdapter($adapter)->getUrl($path);
	}
}
