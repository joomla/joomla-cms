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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Exception\FileExistsException;
use Joomla\Component\Media\Administrator\Exception\FileNotFoundException;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Api Model
 *
 * @since  4.0.0
 */
class ApiModel extends BaseDatabaseModel
{
	/**
	 * Holds available media file adapters.
	 *
	 * @var   ProviderManager
	 * @since  4.0.0
	 */
	private $providerManager = null;

	/**
	 * The available extensions.
	 *
	 * @var   string[]
	 * @since  4.0.0
	 */
	private $allowedExtensions = null;

	/**
	 * Return the requested adapter
	 *
	 * @param   string  $name  Name of the provider
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFile($adapter, $path = '/', $options = array())
	{
		// Add adapter prefix to the file returned
		$file = $this->getAdapter($adapter)->getFile($path);

		// Check if it is a media file
		if ($file->type == 'file' && !$this->isMediaFile($file->path))
		{
			throw new InvalidPathException;
		}

		if (isset($options['url']) && $options['url'] && $file->type == 'file')
		{
			if (isset($options['temp']) && $options['temp'])
			{
				$file->tempUrl = $this->getTemporaryUrl($adapter, $file->path);
			}
			else
			{
				$file->url = $this->getUrl($adapter, $file->path);
			}
		}

		if (isset($options['content']) && $options['content'] && $file->type == 'file')
		{
			$resource = $this->getAdapter($adapter)->getResource($file->path);

			if ($resource)
			{
				$file->content = base64_encode(stream_get_contents($resource));
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
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFiles($adapter, $path = '/', $options = array())
	{
		// Check whether user searching
		if ($options['search'] != null)
		{
			// Do search
			$files = $this->search($adapter, $options['search'], $path, $options['recursive']);
		}
		else
		{
			// Grab files for the path
			$files = $this->getAdapter($adapter)->getFiles($path);
		}

		// Add adapter prefix to all the files to be returned
		foreach ($files as $key => $file)
		{
			// Check if the file is valid
			if ($file->type == 'file' && !$this->isMediaFile($file->path))
			{
				// Remove the file from the data
				unset($files[$key]);
				continue;
			}

			// Check if we need more information
			if (isset($options['url']) && $options['url'] && $file->type == 'file')
			{
				if (isset($options['temp']) && $options['temp'])
				{
					$file->tempUrl = $this->getTemporaryUrl($adapter, $file->path);
				}
				else
				{
					$file->url = $this->getUrl($adapter, $file->path);
				}
			}

			if (isset($options['content']) && $options['content'] && $file->type == 'file')
			{
				$resource = $this->getAdapter($adapter)->getResource($file->path);

				if ($resource)
				{
					$file->content = base64_encode(stream_get_contents($resource));
				}
			}

			$file->path    = $adapter . ":" . $file->path;
			$file->adapter = $adapter;
		}

		// Return array with proper indexes
		return array_values($files);
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in AdapterInterface::createFolder().
	 *
	 * @param   string   $adapter   The adapter
	 * @param   string   $name      The name
	 * @param   string   $path      The folder
	 * @param   boolean  $override  Should the folder being overriden when it exists
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::createFolder()
	 */
	public function createFolder($adapter, $name, $path, $override)
	{
		try
		{
			$file = $this->getFile($adapter, $path . '/' . $name);
		}
		catch (FileNotFoundException $e)
		{
			// Do nothing
		}

		// Check if the file exists
		if (isset($file) && !$override)
		{
			throw new FileExistsException;
		}

		return $this->getAdapter($adapter)->createFolder($name, $path);
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::createFile().
	 *
	 * @param   string   $adapter   The adapter
	 * @param   string   $name      The name
	 * @param   string   $path      The folder
	 * @param   binary   $data      The data
	 * @param   boolean  $override  Should the file being overriden when it exists
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::createFile()
	 */
	public function createFile($adapter, $name, $path, $data, $override)
	{
		try
		{
			$file = $this->getFile($adapter, $path . '/' . $name);
		}
		catch (FileNotFoundException $e)
		{
			// Do nothing
		}

		// Check if the file exists
		if (isset($file) && !$override)
		{
			throw new FileExistsException;
		}

		// Check if it is a media file
		if (!$this->isMediaFile($path . '/' . $name))
		{
			throw new InvalidPathException;
		}

		return $this->getAdapter($adapter)->createFile($name, $path, $data);
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
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::updateFile()
	 */
	public function updateFile($adapter, $name, $path, $data)
	{
		// Check if it is a media file
		if (!$this->isMediaFile($path . '/' . $name))
		{
			throw new InvalidPathException;
		}

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
	 * @since   4.0.0
	 * @throws  \Exception
	 * @see     AdapterInterface::delete()
	 */
	public function delete($adapter, $path)
	{
		$file = $this->getFile($adapter, $path);

		// Check if it is a media file
		if ($file->type == 'file' && !$this->isMediaFile($file->path))
		{
			throw new InvalidPathException;
		}

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
	 * @return  string
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function copy($adapter, $sourcePath, $destinationPath, $force = false)
	{
		return $this->getAdapter($adapter)->copy($sourcePath, $destinationPath, $force);
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
	 * @return  string
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function move($adapter, $sourcePath, $destinationPath, $force = false)
	{
		return $this->getAdapter($adapter)->move($sourcePath, $destinationPath, $force);
	}

	/**
	 * Returns an url for serve media files from adapter.
	 * Url must provide a valid image type to be displayed on Joomla! site.
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $path     The relative path for the file
	 *
	 * @return  string  Permalink to the relative file
	 *
	 * @since   4.0.0
	 * @throws  FileNotFoundException
	 */
	public function getUrl($adapter, $path)
	{
		// Check if it is a media file
		if (!$this->isMediaFile($path))
		{
			throw new InvalidPathException;
		}

		return $this->getAdapter($adapter)->getUrl($path);
	}

	/**
	 * Search for a pattern in a given path
	 *
	 * @param   string  $adapter    The adapter to work on
	 * @param   string  $needle     The search therm
	 * @param   string  $path       The base path for the search
	 * @param   bool    $recursive  Do a recursive search
	 *
	 * @return \stdClass[]
	 *
	 * @since   4.0.0
	 * @throws \Exception
	 */
	public function search($adapter, $needle, $path = '/', $recursive = true)
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
	 * @since   4.0.0
	 * @throws \Exception
	 */
	public function getTemporaryUrl($adapter, $path)
	{
		// Check if it is a media file
		if (!$this->isMediaFile($path))
		{
			throw new InvalidPathException;
		}

		return $this->getAdapter($adapter)->getTemporaryUrl($path);
	}

	/**
	 * Checks if the given path is an allowed media file.
	 *
	 * @param   string  $path  The path to file
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	private function isMediaFile($path)
	{
		// Check if there is an extension available
		if (!strrpos($path, '.'))
		{
			return false;
		}

		// Initialize the allowed extensions
		if ($this->allowedExtensions === null)
		{
			// Get the setting from the params
			$this->allowedExtensions = ComponentHelper::getParams('com_media')->get(
				'upload_extensions',
				'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,TXT,XCF,XLS'
			);

			// Make them an array
			$this->allowedExtensions = explode(',', $this->allowedExtensions);
		}

		// Extract the extension
		$extension = substr($path, strrpos($path, '.') + 1);

		// Check if the extension exists in the allowed extensions
		return in_array($extension, $this->allowedExtensions);
	}
}
