<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Event\FetchMediaItemEvent;
use Joomla\Component\Media\Administrator\Event\FetchMediaItemsEvent;
use Joomla\Component\Media\Administrator\Event\FetchMediaItemUrlEvent;
use Joomla\Component\Media\Administrator\Exception\FileExistsException;
use Joomla\Component\Media\Administrator\Exception\FileNotFoundException;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

/**
 * Api Model
 *
 * @since  4.0.0
 */
class ApiModel extends BaseDatabaseModel
{
	use ProviderManagerHelperTrait;

	/**
	 * The available extensions.
	 *
	 * @var   string[]
	 * @since  4.0.0
	 */
	private $allowedExtensions = null;

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
	public function getFile($adapter, $path = '/', $options = [])
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
			$file->url = $this->getUrl($adapter, $file->path);
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

		$event = new FetchMediaItemEvent('onFetchMediaItem', ['item' => $file]);
		Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);

		return $event->getArgument('item');
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
	public function getFiles($adapter, $path = '/', $options = [])
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
				$file->url = $this->getUrl($adapter, $file->path);
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

		// Make proper indexes
		$files = array_values($files);

		$event = new FetchMediaItemsEvent('onFetchMediaItems', ['items' => $files]);
		Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);

		return $event->getArgument('items');
	}

	/**
	 * Creates a folder with the given name in the given path. More information
	 * can be found in AdapterInterface::createFolder().
	 *
	 * @param   string   $adapter   The adapter
	 * @param   string   $name      The name
	 * @param   string   $path      The folder
	 * @param   boolean  $override  Should the folder being overridden when it exists
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

		$app               = Factory::getApplication();
		$object            = new CMSObject;
		$object->adapter   = $adapter;
		$object->name      = $name;
		$object->path      = $path;

		PluginHelper::importPlugin('content');

		$result = $app->triggerEvent('onContentBeforeSave', ['com_media.folder', $object, true, $object]);

		if (in_array(false, $result, true))
		{
			throw new \Exception($object->getError());
		}

		$object->name = $this->getAdapter($object->adapter)->createFolder($object->name, $object->path);

		$app->triggerEvent('onContentAfterSave', ['com_media.folder', $object, true, $object]);

		return $object->name;
	}

	/**
	 * Creates a file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::createFile().
	 *
	 * @param   string   $adapter   The adapter
	 * @param   string   $name      The name
	 * @param   string   $path      The folder
	 * @param   string   $data      The data
	 * @param   boolean  $override  Should the file being overridden when it exists
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

		$app               = Factory::getApplication();
		$object            = new CMSObject;
		$object->adapter   = $adapter;
		$object->name      = $name;
		$object->path      = $path;
		$object->data      = $data;
		$object->extension = strtolower(File::getExt($name));

		PluginHelper::importPlugin('content');

		// Also include the filesystem plugins, perhaps they support batch processing too
 		PluginHelper::importPlugin('media-action');

		$result = $app->triggerEvent('onContentBeforeSave', ['com_media.file', $object, true, $object]);

		if (in_array(false, $result, true))
		{
			throw new \Exception($object->getError());
		}

		$object->name = $this->getAdapter($object->adapter)->createFile($object->name, $object->path, $object->data);

		$app->triggerEvent('onContentAfterSave', ['com_media.file', $object, true, $object]);

		return $object->name;
	}

	/**
	 * Updates the file with the given name in the given path with the data. More information
	 * can be found in AdapterInterface::updateFile().
	 *
	 * @param   string  $adapter  The adapter
	 * @param   string  $name     The name
	 * @param   string  $path     The folder
	 * @param   string  $data     The data
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

		$app               = Factory::getApplication();
		$object            = new CMSObject;
		$object->adapter   = $adapter;
		$object->name      = $name;
		$object->path      = $path;
		$object->data      = $data;
		$object->extension = strtolower(File::getExt($name));

		PluginHelper::importPlugin('content');

		// Also include the filesystem plugins, perhaps they support batch processing too
 		PluginHelper::importPlugin('media-action');

		$result = $app->triggerEvent('onContentBeforeSave', ['com_media.file', $object, false, $object]);

		if (in_array(false, $result, true))
		{
			throw new \Exception($object->getError());
		}

		$this->getAdapter($object->adapter)->updateFile($object->name, $object->path, $object->data);

		$app->triggerEvent('onContentAfterSave', ['com_media.file', $object, false, $object]);
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

		$type              = $file->type === 'file' ? 'file' : 'folder';
		$app               = Factory::getApplication();
		$object            = new CMSObject;
		$object->adapter   = $adapter;
		$object->path      = $path;

		PluginHelper::importPlugin('content');

		// Also include the filesystem plugins, perhaps they support batch processing too
 		PluginHelper::importPlugin('media-action');

		$result = $app->triggerEvent('onContentBeforeDelete', ['com_media.' . $type, $object]);

		if (in_array(false, $result, true))
		{
			throw new \Exception($object->getError());
		}

		$this->getAdapter($object->adapter)->delete($object->path);

		$app->triggerEvent('onContentAfterDelete', ['com_media.' . $type, $object]);
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
	 * Returns a url for serve media files from adapter.
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

		$url = $this->getAdapter($adapter)->getUrl($path);

		$event = new FetchMediaItemUrlEvent('onFetchMediaFileUrl', ['adapter' => $adapter, 'path' => $path, 'url' => $url]);
		Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);

		return $event->getArgument('url');
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
			// Get options from the input or fallback to images only
			$mediaTypes = explode(',', Factory::getApplication()->input->getString('mediatypes', '0'));
			$types      = [];
			$extensions = [];

			// Default to showing all supported formats
			if (count($mediaTypes) === 0) {
				$mediaTypes = ['0', '1', '2', '3'];
			}

			array_map(
				function ($mediaType) use (&$types) {
					switch ($mediaType) {
						case '0':
							$types[] = 'images';
							break;
						case '1':
							$types[] = 'audios';
							break;
						case '2':
							$types[] = 'videos';
							break;
						case '3':
							$types[] = 'documents';
							break;
						default:
							break;
					}
				},
				$mediaTypes
			);

			$images = array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'image_extensions',
						'bmp,gif,jpg,jpeg,png,webp'
					)
				)
			);
			$audios = array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'audio_extensions',
						'mp3,m4a,mp4a,ogg'
					)
				)
			);
			$videos = array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'video_extensions',
						'mp4,mp4v,mpeg,mov,webm'
					)
				)
			);
			$documents = array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'doc_extensions',
						'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'
					)
				)
			);

			foreach ($types as $type) {
				if (in_array($type, ['images', 'audios', 'videos', 'documents'])) {
					$extensions = array_merge($extensions, ${$type});
				}
			}

			// Make them an array
			$this->allowedExtensions = $extensions;
		}

		// Extract the extension
		$extension = strtolower(substr($path, strrpos($path, '.') + 1));

		// Check if the extension exists in the allowed extensions
		return in_array($extension, $this->allowedExtensions);
	}
}
