<?php
/**
 * Created by PhpStorm.
 * User: kasun
 * Date: 6/27/17
 * Time: 11:16 PM
 */

namespace Joomla\Plugin\Filesystem\Dropbox\Adapter;

defined('_JEXEC') or die;

\JLoader::import('filesystem.dropbox.vendor.autoload', JPATH_PLUGINS);

use Joomla\CMS\Uri\Uri;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Adapter\FileNotFoundException;

/**
 * Class JoomlaDropboxAdapter
 * @package Joomla\Plugin\Filesystem\Dropbox\Adapter
 */
class JoomlaDropboxAdapter implements AdapterInterface
{
	/**
	 * Supported extension for thumbnails
	 *
	 * @var array
	 * @since   __DEPLOY_VERSION__
	 */
	private $supportedThumbnailImageFormats = array('jpg', 'jpeg', 'png', 'tiff', 'tif', 'gif' , 'bmp');

	/**
	 * Dropbox client to work with
	 *
	 * @var \Srmklive\Dropbox\Client\DropboxClient
	 * @since   __DEPLOY_VERSION__
	 */
	private $client = null;

	/**
	 * Dropbox adapter to work with
	 *
	 * @var \Srmklive\Dropbox\Adapter\DropboxAdapter
	 * @since   __DEPLOY_VERSION__
	 */
	private $adapter = null;

	/**
	 * Flysystem driver
	 *
	 * @var \League\Flysystem\Filesystem
	 * @since   __DEPLOY_VERSION__
	 */
	private $dropbox = null;

	/**
	 * DropboxAdapter constructor.
	 *
	 * @param   string $apiToken  API Token received from dropbox API
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($apiToken)
	{
		$this->client  = $this->getClient($apiToken);
		$this->adapter = $this->getAdapter($this->client);
		$this->dropbox = $this->getDropbox($this->adapter);
	}

	/**
	 * Returns a client for dropbox
	 *
	 * @param   string  $apiToken API Token obtained from the dropbox API
	 *
	 * @return \Srmklive\Dropbox\Client\DropboxClient
	 * @since   __DEPLOY_VERSION__
	 */
	private function getClient($apiToken)
	{
		return new \Srmklive\Dropbox\Client\DropboxClient($apiToken);
	}

	/**
	 * Returns an adapter for flysystem
	 *
	 * @param   \Srmklive\Dropbox\Client\DropboxClient $client Client object
	 *
	 * @return \Srmklive\Dropbox\Adapter\DropboxAdapter
	 * @since   __DEPLOY_VERSION__
	 */
	private function getAdapter($client)
	{
		return new \Srmklive\Dropbox\Adapter\DropboxAdapter($client);
	}

	/**
	 * Returns a flysystem adapter
	 *
	 * @param   \Srmklive\Dropbox\Adapter\DropboxAdapter $adapter Adapter object
	 *
	 * @return \League\Flysystem\Filesystem
	 * @since   __DEPLOY_VERSION__
	 */
	private function getDropbox($adapter)
	{
		return new \League\Flysystem\Filesystem($adapter);
	}

	/**
	 * Returns the requested file or folder. The returned object
	 * has the following properties available:
	 * - type:          The type can be file or dir
	 * - name:          The name of the file
	 * - path:          The relative path to the root
	 * - extension:     The file extension
	 * - size:          The size of the file
	 * - create_date:   The date created
	 * - modified_date: The date modified
	 * - mime_type:     The mime type
	 * - width:         The width, when available
	 * - height:        The height, when available
	 *
	 * If the path doesn't exist a FileNotFoundException is thrown.
	 *
	 * @param   string $path The path to the file or folder
	 *
	 * @return  \stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function getFile($path = '/')
	{
		$path = \JPath::clean($path);

		if (!$this->dropbox->has($path))
		{
			throw new FileNotFoundException("File not found");
		}

		$meta = $this->client->getMetaData($path);
		return $this->getFileInfo($meta);
	}

	/**
	 * Returns the folders and files for the given path. The returned objects
	 * have the following properties available:
	 * - type:          The type can be file or dir
	 * - name:          The name of the file
	 * - path:          The relative path to the root
	 * - extension:     The file extension
	 * - size:          The size of the file
	 * - create_date:   The date created
	 * - modified_date: The date modified
	 * - mime_type:     The mime type
	 * - width:         The width, when available
	 * - height:        The height, when available
	 *
	 * If the path doesn't exist a FileNotFoundException is thrown.
	 *
	 * @param   string $path   The folder
	 * @param   string $filter The filter
	 *
	 * @return  \stdClass[]
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function getFiles($path = '/', $filter = '')
	{
		if ($path != '/' && !$this->dropbox->has($path))
		{
			throw new FileNotFoundException("File not found");
		}

		$response = $this->client->listFolder($path);
		$files = [];

		foreach ($response['entries'] as $fileEntry)
		{
			$files[] = $this->getFileInfo($fileEntry);
		}

		return $files;
	}

	/**
	 * Extract file information from an entry of dropbox
	 *
	 * @param   array  $fileEntry  File entry from dropbox
	 *
	 * @return \stdClass
	 * @since   __DEPLOY_VERSION__
	 */
	private function getFileInfo($fileEntry)
	{
		$file                          = new \stdClass;
		$file->type                    = ($fileEntry['.tag'] == 'file' ? 'file' : 'dir');
		$file->name                    = $fileEntry['name'];
		$file->path                    = $fileEntry['path_display'];
		$file->size                    = 0;
		$file->width                   = 0;
		$file->height                  = 0;
		$file->create_date_formatted   = '';
		$file->modified_date_formatted = '';
		$file->create_date             = '';
		$file->modified_date           = '';
		$file->extension               = '';
		$file->thumb_path              = '';

		// Dropbox does not support Mime Types
		$file->mime_type               = '';

		if (isset($fileEntry['size']))
		{
			$file->size = $fileEntry['size'];
		}

		if (isset($fileEntry['client_modified']))
		{
			$file->create_date_formatted = $fileEntry['client_modified'];
			$file->create_date = $fileEntry['client_modified'];
		}

		if (isset($fileEntry['server_modified']))
		{
			$file->modified_date_formatted = $fileEntry['server_modified'];
			$file->modified_date = $fileEntry['server_modified'];
		}

		if (isset($fileEntry['media_info']))
		{
			$mediaInfo = $fileEntry['media_info'];
			if (isset($mediaInfo['metadata']))
			{
				$metaData = $mediaInfo['metadata'];
				if (isset($metaData['dimensions']))
				{
					$dimensions   = $metaData['dimensions'];
					$file->width  = $dimensions['width'];
					$file->height = $dimensions['height'];
				}
			}
		}

		if ($file->type == 'file')
		{
			$file->extension = substr(strrchr($file->name,'.'),1);
		}

		if (in_array($file->extension, $this->supportedThumbnailImageFormats))
		{
			$file->thumb_path = $this->getThumbnailUrl($fileEntry['id'], $file->modified_date_formatted , $file->path);
		}

		return $file;
	}

	/**
	 * @param   string  $id            File ID provided by dropbox
	 * @param   string  $timeModified  Time modified
	 * @param   string  $path          Path to file
	 *
	 * @return string
	 * @since   __DEPLOY_VERSION__
	 */
	private function getThumbnailUrl($id, $timeModified , $path)
	{

		$name = explode(":", $id)[1];
		$timeStamp = strtotime($timeModified);
		$filePath = \JPath::clean(JPATH_SITE . '/media/plg_filesystem_dropbox/.thumb_cache/' . $name . $timeStamp . '.jpg' , '/');

		if (!\JFile::exists($filePath))
		{
			$content = $this->client->getThumbnail($path, 'jpeg', 'w128h128');
			\JFile::write($filePath, $content);
		}

		return Uri::root() . \JPath::clean( 'media/plg_filesystem_dropbox/.thumb_cache/'. $name . $timeStamp . '.jpg', '/');
	}


	/**
	 * Creates a folder with the given name in the given path.
	 *
	 * @param   string $name The name
	 * @param   string $path The folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createFolder($name, $path)
	{
		$this->client->createFolder(\JPath::clean($path . '/' .$name));
	}

	/**
	 * Creates a file with the given name in the given path with the data.
	 *
	 * @param   string $name The name
	 * @param   string $path The folder
	 * @param   binary $data The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function createFile($name, $path, $data)
	{
		$filePath = \JPath::clean($path . '/' . $name);

		$response = $this->client->upload($filePath, $data);

		if (!isset($response['.tag']))
		{
			throw new \Exception("Upload failed");
		}
	}

	/**
	 * Updates the file with the given name in the given path with the data.
	 *
	 * @param   string $name The name
	 * @param   string $path The folder
	 * @param   binary $data The data
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function updateFile($name, $path, $data)
	{
		if (!$this->dropbox->has($path . '/' . $name))
		{
			throw new FileNotFoundException("File not found");
		}

		$response = $this->client->upload($path . '/' . $name, $data, 'update');

		if ($response->getStatusCode() != 200)
		{
			throw new \Exception("Deletion failed");
		}
	}

	/**
	 * Deletes the folder or file of the given path.
	 *
	 * @param   string $path The path to the file or folder
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function delete($path)
	{
		$response = $this->client->delete($path);

		if ($response->getStatusCode() != 200)
		{
			throw new \Exception("Deletion failed");
		}
	}

	/**
	 * Moves a file or folder from source to destination
	 *
	 * @param   string $sourcePath      The source path
	 * @param   string $destinationPath The destination path
	 * @param   bool   $force           Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function move($sourcePath, $destinationPath, $force = false)
	{
		$response = $this->client->move($sourcePath, $destinationPath);

		if ($response != 200)
		{
			throw new \Exception("Move failed");
		}
	}

	/**
	 * Copies a file or folder from source to destination
	 *
	 * @param   string $sourcePath      The source path
	 * @param   string $destinationPath The destination path
	 * @param   bool   $force           Force to overwrite
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	public function copy( $sourcePath, $destinationPath, $force = false)
	{
		$response = $this->client->copy($sourcePath, $destinationPath);

		if ($response != 200)
		{
			throw new \Exception("Move failed");
		}
	}

	/**
	 * Returns a permanent link for media file.
	 *
	 * @param   string $path The path to file
	 *
	 * @return string
	 * @since   __DEPLOY_VERSION__
	 * @throws FileNotFoundException
	 */
	public function getUrl($path)
	{
		return $this->client->getTemporaryLink($path);
	}

}