<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Component\Media\Administrator\Exception\FileExistsException;
use Joomla\Component\Media\Administrator\Exception\FileNotFoundException;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;

\JLoader::import('joomla.filesystem.file');

/**
 * Api Media Controller
 *
 * This is NO public api controller, it is internal for the com_media component only!
 *
 * @since  4.0.0
 */
class ApiController extends BaseController
{
	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task  The task to perform. If no matching task is found, the '__default' task is executed, if defined.
	 *
	 * @return  mixed   The value returned by the called method.
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function execute($task)
	{
		$method = $this->input->getMethod();

		$this->task   = $task;
		$this->method = $method;

		try
		{
			// Check token for requests which do modify files (all except get requests)
			if ($method !== 'GET' && !Session::checkToken('json'))
			{
				throw new \InvalidArgumentException(\JText::_('JINVALID_TOKEN'), 403);
			}

			$doTask = strtolower($method) . ucfirst($task);

			// Record the actual task being fired
			$this->doTask = $doTask;

			if (!in_array($this->doTask, $this->taskMap))
			{
				throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 405);
			}

			$data = $this->$doTask();

			// Return the data
			$this->sendResponse($data);
		}
		catch (FileNotFoundException $e)
		{
			$this->sendResponse($e, 404);
		}
		catch (FileExistsException $e)
		{
			$this->sendResponse($e, 409);
		}
		catch (InvalidPathException $e)
		{
			$this->sendResponse($e, 400);
		}
		catch (\Exception $e)
		{
			$errorCode = 500;

			if ($e->getCode() > 0)
			{
				$errorCode = $e->getCode();
			}

			$this->sendResponse($e, $errorCode);
		}
	}

	/**
	 * Files Get Method
	 *
	 * Examples:
	 *
	 * - GET a list of folders below the root:
	 * 		index.php?option=com_media&task=api.files
	 * 		/api/files
	 * - GET a list of files and subfolders of a given folder:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 * - GET a list of files and subfolders of a given folder for a given search term:
	 *   use recursive=1 to search recursively in the working directory
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop&search=apple
	 * 		/api/files/sampledata/fruitshop?search=apple
	 *   To look up in same working directory set flag recursive=0
	 *      index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop&search=apple&recursive=0
	 * 		/api/files/sampledata/fruitshop?search=apple&recursive=0
	 * - GET file information for a specific file:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 * - GET a temporary URL to a given file
	 *      index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg&url=1&temp=1
	 * 		/api/files/sampledata/fruitshop/test.jpg&url=1&temp=1
	 * - GET a temporary URL to a given file
	 *      index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg&url=1
	 * 		/api/files/sampledata/fruitshop/test.jpg&url=1
	 *
	 * @return  array  The data to send with the response
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getFiles()
	{
		// Grab options
		$options              = array();
		$options['url']       = $this->input->getBool('url', false);
		$options['temp']      = $this->input->getBool('temp', false);
		$options['search']    = $this->input->getString('search', '');
		$options['recursive'] = $this->input->getBool('recursive', true);
		$options['content']   = $this->input->getBool('content', false);

		return $this->getModel()->getFiles($this->getAdapter(), $this->getPath(), $options);
	}

	/**
	 * Files delete Method
	 *
	 * Examples:
	 *
	 * - DELETE an existing folder in a specific folder:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test
	 * 		/api/files/sampledata/fruitshop/test
	 * - DELETE an existing file in a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * @return  null
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function deleteFiles()
	{
		$this->getModel()->delete($this->getAdapter(), $this->getPath());

		return null;
	}

	/**
	 * Files Post Method
	 *
	 * Examples:
	 *
	 * - POST a new file or folder into a specific folder, the file or folder information is returned:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 *
	 * 		New file body:
	 * 		{
	 * 			"name": "test.jpg",
	 * 			"content":"base64 encoded image"
	 * 		}
	 * 		New folder body:
	 * 		{
	 * 			"name": "test",
	 * 		}
	 *
	 * @return  array  The data to send with the response
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function postFiles()
	{
		$adapter      = $this->getAdapter();
		$path         = $this->getPath();
		$content      = $this->input->json;
		$name         = $content->getString('name');
		$mediaContent = base64_decode($content->get('content', '', 'raw'));
		$override     = $content->get('override', false);

		if ($mediaContent)
		{
			$this->checkContent();

			// A file needs to be created
			$name = $this->getModel()->createFile($adapter, $name, $path, $mediaContent, $override);
		}
		else
		{
			// A file needs to be created
			$name = $this->getModel()->createFolder($adapter, $name, $path, $override);
		}

		return $this->getModel()->getFile($adapter, $path . '/' . $name);
	}

	/**
	 * Files Put method
	 *
	 * Examples:
	 *
	 * - PUT a media file, the file or folder information is returned:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * 		Update file body:
	 * 		{
	 * 			"content":"base64 encoded image"
	 * 		}
	 *
	 * - PUT move a file, folder to another one
	 *     path : will be taken as the source
	 *     index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 	   /api/files/sampledata/fruitshop/test.jpg
	 *
	 *     JSON body:
	 *     {
	 *          "newPath" : "/path/to/destination",
	 *          "move"    : "1"
	 *     }
	 *
	 * - PUT copy a file, folder to another one
	 *     path : will be taken as the source
	 *     index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 	   /api/files/sampledata/fruitshop/test.jpg
	 *
	 *     JSON body:
	 *     {
	 *          "newPath" : "/path/to/destination",
	 *          "move"    : "0"
	 *     }
	 *
	 * @return  array  The data to send with the response
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function putFiles()
	{
		$adapter = $this->getAdapter();
		$path    = $this->getPath();

		$content      = $this->input->json;
		$name         = basename($path);
		$mediaContent = base64_decode($content->get('content', '', 'raw'));
		$newPath      = $content->getString('newPath', null);
		$move         = $content->get('move', true);

		if ($mediaContent != null)
		{
			$this->checkContent();

			$this->getModel()->updateFile($adapter, $name, str_replace($name, '', $path), $mediaContent);
		}

		if ($newPath != null)
		{
			list($destinationAdapter, $destinationPath) = explode(':', $newPath, 2);

			if ($move)
			{
				$destinationPath = $this->getModel()->move($adapter, $path, $destinationPath, false);
			}
			else
			{
				$destinationPath = $this->getModel()->copy($adapter, $path, $destinationPath, false);
			}

			$path = $destinationPath;
		}

		return $this->getModel()->getFile($adapter, $path);
	}

	/**
	 * Send the given data as JSON response in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":[{"type":"dir","name":"banners","path":"//"}]}
	 *
	 * @param   mixed   $data          The data to send
	 * @param   number  $responseCode  The response code
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function sendResponse($data = null, $responseCode = 200)
	{
		// Set the correct content type
		$this->app->setHeader('Content-Type', 'application/json');

		// Set the status code for the response
		http_response_code($responseCode);

		// Send the data
		echo new JsonResponse($data);

		$this->app->close();
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Model|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getModel($name = 'Api', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Performs various checks if it is allowed to save the content.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	private function checkContent()
	{
		if (!Factory::getUser()->authorise('core.create', 'com_media'))
		{
			throw new \Exception(\JText::_('COM_MEDIA_ERROR_CREATE_NOT_PERMITTED'), 403);
		}

		$params = ComponentHelper::getParams('com_media');

		$helper       = new MediaHelper;
		$serverlength = $this->input->server->get('CONTENT_LENGTH');

		if ($serverlength > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $serverlength > $helper->toBytes(ini_get('upload_max_filesize'))
			|| $serverlength > $helper->toBytes(ini_get('post_max_size'))
			|| $serverlength > $helper->toBytes(ini_get('memory_limit')))
		{
			throw new \Exception(\JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 403);
		}
	}

	/**
	 * Get the Adapter.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	private function getAdapter()
	{
		$parts = explode(':', $this->input->getString('path', ''), 2);

		if (count($parts) < 1)
		{
			return null;
		}

		return $parts[0];
	}

	/**
	 * Get the Path.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	private function getPath()
	{
		$parts = explode(':', $this->input->getString('path', ''), 2);

		if (count($parts) < 2)
		{
			return null;
		}

		return $parts[1];
	}
}
