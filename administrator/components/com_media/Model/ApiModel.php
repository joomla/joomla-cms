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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Adapter\FileNotFoundException;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class ApiModel extends BaseModel
{
	/**
	 * Holds available media file adapters.
	 *
	 * @var   ProviderManager
	 * @since  __DEPLOY_VERSION__
	 */
	private $providerManager = null;

	/**
	 * Return the requested adapter
	 *
	 * @param   string  $name  Name of the provider
	 *
	 * @since   __DEPLOY_VERSION__
	 * @return AdapterInterface
	 *
	 * @throws \Exception
	 */
	private function getAdapter($name)
	{
		if ($this->providerManager == null)
		{
			$this->providerManager = new ProviderManager;

			// Fire the event to get the results
			$eventParameters = ['context' => 'AdapterManager', 'providerManager' => $this->providerManager];
			$event = new MediaProviderEvent('onSetupProviders', $eventParameters);
			PluginHelper::importPlugin('filesystem');
			Factory::getApplication()->triggerEvent('onSetupProviders', $event);
		}

		return $this->providerManager->getAdapter($name);
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in AdapterInterface::getFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to the file or folder
	 * @param   array   $options  The options
	 *
	 * @return  \stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFile($adapter, $path = '/', $options = array())
	{
		// Add adapter prefix to the file returned
		$file = $this->getAdapter($adapter)->getFile($path);

		if (isset($options['url']) && $options['url'] && $file->type == 'file')
		{
			if (isset($options['temp']) && $options['temp'])
			{
				$file->tempUrl = $this->getTemporaryUrl($adapter, $file->path);
			}
			else
			{
				$file->url = $this->getUrl( $adapter, $file->path );
			}
		}

		$file->path    = $adapter . ":" . $file->path;
		$file->adapter = $adapter;

		return $file;
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in AdapterInterface::getFiles().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The folder
	 * @param   array   $options  The options
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFiles($adapter, $path = '/', $options = array())
	{
		// Check whether user searching
		if ($options['search'] != null)
		{
			// Do search
			$files = $this->search($adapter, $path, $options['search'], $options['recursive']);
		}
		else
		{
			// Grab files for the path
			$files = $this->getAdapter($adapter)->getFiles($path);
		}

		// Add adapter prefix to all the files to be returned
		foreach ($files as $file)
		{
			// If requested add options
			// Url can be provided for a file
			if (isset($options['url']) && $options['url'] && $file->type == 'file')
			{
				if (isset($options['temp']) && $options['temp'])
				{
					$file->tempUrl = $this->getTemporaryUrl($adapter, $file->path);
				}
				else
				{
					$file->url = $this->getUrl( $adapter, $file->path );
				}
			}

			$file->path    = $adapter . ":" . $file->path;
			$file->adapter = $adapter;
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

	/**
	 * Search for a pattern in a given path
	 *
	 * @param   string  $adapter    The adapter to work on
	 * @param   string  $path       The base path for the search
	 * @param   string  $needle     The path to file
	 * @param   bool    $recursive  Do a recursive search
	 *
	 * @return \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function search($adapter, $path = '/', $needle, $recursive = true)
	{
		return $this->getAdapter($adapter)->search($path, $needle, $recursive);
	}

	/**
	 * Returns a temporary url for the given path.
	 * This is used internally in media manager
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The path to file
	 *
	 * @return string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws \FileNotFoundException
	 */
	public function getTemporaryUrl($adapter, $path)
	{
		return $this->getAdapter($adapter)->getTemporaryUrl($path);
	}
}
