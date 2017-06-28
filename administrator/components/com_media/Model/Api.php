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

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class Api extends Model
{
	/**
	 * The local file adapter to work with.
	 *
	 * @var    AdapterInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $adapter = null;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MvcFactoryInterface  $factory  The factory.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		if (!isset($config['fileadapter']))
		{
			// Import Local file system plugin
			PluginHelper::importPlugin('filesystem');

			$app = \JFactory::getApplication();

			$results = $app->triggerEvent('onFileSystemGetAdapters');

			if ($results != null)
			{
				$config['fileadapter'] = $results[0];
			}
		}

		if (isset($config['fileadapter']))
		{
			$this->adapter = $config['fileadapter'];
		}
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in AdapterInterface::getFile().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFile($path = '/')
	{
		return $this->adapter->getFile($path);
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in AdapterInterface::getFiles().
	 *
	 * @param   string  $path    The folder
	 * @param   string  $filter  The filter
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::getFile()
	 */
	public function getFiles($path = '/', $filter = '')
	{
		if (!$this->adapter)
		{
			return array();
		}

		return $this->adapter->getFiles($path, $filter);
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in AdapterInterface::createFolder().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::createFolder()
	 */
	public function createFolder($name, $path)
	{
		$this->adapter->createFolder($name, $path);

		return $name;
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::createFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  string  The new file name
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::createFile()
	 */
	public function createFile($name, $path, $data)
	{
		$this->adapter->createFile($name, $path, $data);

		return $name;
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::updateFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::updateFile()
	 */
	public function updateFile($name, $path, $data)
	{
		$this->adapter->updateFile($name, $path, $data);
	}

	/**
	 * Deletes the folder or file of the given path. More information
	 * can be found in AdapterInterface::delete().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 * @see     AdapterInterface::delete()
	 */
	public function delete($path)
	{
		$this->adapter->delete($path);
	}

	/**
	 * Copies file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function copy($sourcePath, $destinationPath, $force = false)
	{
		$this->adapter->copy($sourcePath, $destinationPath, $force);
	}

	/**
	 * Moves file or folder from source path to destination path
	 * If forced, existing files/folders would be overwritten
	 *
	 * @param   string  $sourcePath       Source path of the file or folder (relative)
	 * @param   string  $destinationPath  Destination path(relative)
	 * @param   bool    $force            Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function move($sourcePath, $destinationPath, $force = false)
	{
		 $this->adapter->move($sourcePath, $destinationPath, $force);
	}
}
