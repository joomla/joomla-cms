<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Cms\Model\Model;

/**
 * Api Model
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaModelApi extends Model
{
	/**
	 * The local file adapter to work with.
	 *
	 * @var MediaFileAdapterInterface
	 */
	protected $adapter = null;
	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (!isset($config['fileadapter']))
		{
			// Compile the root path
			$root = JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('file_path', 'images');
			$root = rtrim($root) . '/';

			// Default to the local adapter
			$config['fileadapter'] = new MediaFileAdapterLocal($root);
		}

		$this->adapter = $config['fileadapter'];
	}

	/**
	 * Returns the requested file or folder information. More information
	 * can be found in MediaFileAdapterInterface::getFile().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFile($path = '/')
	{
		return $this->adapter->getFile($path);
	}

	/**
	 * Returns the folders and files for the given path. More information
	 * can be found in MediaFileAdapterInterface::getFiles().
	 *
	 * @param   string  $path    The folder
	 * @param   string  $filter  The filter
	 *
	 * @return  stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::getFile()
	 */
	public function getFiles($path = '/', $filter = '')
	{
		return $this->adapter->getFiles($path, $filter);
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in MediaFileAdapterInterface::createFolder().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFolder()
	 */
	public function createFolder($name, $path)
	{
		$this->adapter->createFolder($name, $path);
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::createFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::createFile()
	 */
	public function createFile($name, $path, $data)
	{
		$name = $this->getSafeName($name);

		$this->checkContent($name, $data);

		$this->adapter->createFile($name, $path, $data);
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in MediaFileAdapterInterface::updateFile().
	 *
	 * @param   string  $name  The name
	 * @param   string  $path  The folder
	 * @param   binary  $data  The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::updateFile()
	 */
	public function updateFile($name, $path, $data)
	{
		$this->adapter->updateFile($name, $path, $data);
	}

	/**
	 * Deletes the folder or file of the given path. More information
	 * can be found in MediaFileAdapterInterface::delete().
	 *
	 * @param   string  $path  The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 * @see     MediaFileAdapterInterface::delete()
	 */
	public function delete($path)
	{
		$this->adapter->delete($path);
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
		$name = preg_replace(array("/[\\s]/", '/[^a-zA-Z0-9_]/'), array('_', ''), $name) . $extension;

		return $name;
	}
}
