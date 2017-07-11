<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\Media\Administrator\Adapter\FileNotFoundException;

\JLoader::import('joomla.filesystem.file');

/**
 * Api Media Controller
 *
 * This is NO public api controller, it is internal for the com_media component only!
 *
 * @since  __DEPLOY_VERSION__
 */
class Api extends Controller
{
	/**
	 * Api endpoint for the media manager front end. The HTTP methods GET, PUT, POST and DELETE
	 * are supported.
	 *
	 * The following query parameters are processed:
	 * - path: The path of the resource, if not set then the default / is taken.
	 *
	 * Some examples with a more understandable rest url equivalent:
	 * - GET a list of folders below the root:
	 * 		index.php?option=com_media&task=api.files
	 * 		/api/files
	 * - GET a list of files and subfolders of a given folder:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 * - GET a list of files and subfolders of a given folder for a given filter:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop&filter=apple
	 * 		/api/files/sampledata/fruitshop?filter=apple
	 * - GET file information for a specific file:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
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
	 * - PUT a media file, the file or folder information is returned:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * 		Update file body:
	 * 		{
	 * 			"content":"base64 encoded image"
	 * 		}
	 *
	 * - DELETE an existing folder in a specific folder:
	 * 		index.php?option=com_media&task=api.files&format=json&path=/sampledata/fruitshop/test
	 * 		/api/files/sampledata/fruitshop/test
	 * - DELETE an existing file in a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function files()
	{
		// Get the required variables
		list($adapterInfo, $path) = explode(':', $this->input->getString('path', ''));
		$adapter = $adapterInfo;

		// Determine the method
		$method = strtolower($this->input->getMethod() ? : 'GET');

		try
		{
			// Check token for requests which do modify files (all except get requests)
			if ($method != 'get' && !\JSession::checkToken('json'))
			{
				throw new \InvalidArgumentException(\JText::_('JINVALID_TOKEN'), 403);
			}

			// Gather the data according to the method
			switch ($method)
			{
				case 'get':
					// Grab options
					$options = array();
					$options['url'] = $this->input->getBool('url', false);

					$data = $this->getModel()->getFiles($adapter, $path, $this->input->getWord('filter'), $options);

					break;
				case 'delete':
					$this->getModel()->delete($adapter, $path);

					// Define this for capability with other cases
					$data = null;
					break;
				case 'post':
					$content      = $this->input->json;
					$name         = $content->getString('name');
					$mediaContent = base64_decode($content->get('content', '', 'raw'));

					$name = $this->getSafeName($name);
					if ($mediaContent)
					{
						$this->checkContent($name, $mediaContent);

						// A file needs to be created
						$name = $this->getModel()->createFile($adapter, $name, $path, $mediaContent);
					}
					else
					{
						// A file needs to be created
						$name = $this->getModel()->createFolder($adapter, $name, $path);
					}

					$data = $this->getModel()->getFile($adapter, $path . '/' . $name);
					break;
				case 'put':
					$content      = $this->input->json;
					$name         = basename($path);
					$mediaContent = base64_decode($content->get('content', '', 'raw'));

					$this->checkContent($name, $mediaContent);

					$this->getModel()->updateFile($adapter, $name, str_replace($name, '', $path), $mediaContent);

					$data = $this->getModel()->getFile($adapter, $path);
					break;
				default:
					throw new \BadMethodCallException('Method not supported yet!');
			}

			// Return the data
			$this->sendResponse($data);
		}
		catch (FileNotFoundException $e)
		{
			$this->sendResponse($e, 404);
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
	 * Send the given data as JSON response in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":[{"type":"dir","name":"banners","path":"//"}]}
	 *
	 * @param   mixed   $data          The data to send
	 * @param   number  $responseCode  The response code
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function sendResponse($data = null, $responseCode = 200)
	{
		// Set the correct content type
		\JFactory::getApplication()->setHeader('Content-Type', 'application/json');

		// Set the status code for the response
		http_response_code($responseCode);

		// Send the data
		echo new JsonResponse($data);
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Api', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Performs various check if it is allowed to save the content with the given name.
	 *
	 * @param   string  $name          The filename
	 * @param   string  $mediaContent  The media content
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	private function checkContent($name, $mediaContent)
	{
		if (!\JFactory::getUser()->authorise('core.create', 'com_media'))
		{
			throw new \Exception(\JText::_('COM_MEDIA_ERROR_CREATE_NOT_PERMITTED'), 403);
		}

		$params = ComponentHelper::getParams('com_media');

		$helper = new MediaHelper;
		$serverlength = $this->input->server->get('CONTENT_LENGTH');

		if ($serverlength > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $serverlength > $helper->toBytes(ini_get('upload_max_filesize'))
			|| $serverlength > $helper->toBytes(ini_get('post_max_size'))
			|| $serverlength > $helper->toBytes(ini_get('memory_limit')))
		{
			throw new \Exception(\JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'));
		}

		// @todo find a better way to check the input, by not writing the file to the disk
		$tmpFile = \JFactory::getApplication()->getConfig()->get('tmp_path') . '/' . uniqid($name);

		if (!\JFile::write($tmpFile, $mediaContent))
		{
			throw new \Exception(\JText::_('JLIB_MEDIA_ERROR_UPLOAD_INPUT'));
		}

		$name = $this->getSafeName($name);
		if (!$helper->canUpload(array('name' => $name, 'size' => count($mediaContent), 'tmp_name' => $tmpFile), 'com_media'))
		{
			\JFile::delete($tmpFile);

			throw new \Exception(\JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'), 403);
		}

		\JFile::delete($tmpFile);
	}

	/**
	 * Creates a safe file name for the given name.
	 *
	 * @param   string  $name  The filename
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Exception
	 */
	private function getSafeName($name)
	{
		// Make the filename safe
		$name = \JFile::makeSafe($name);

		// Transform filename to punycode
		$name = \JStringPunycode::toPunycode($name);

		$extension = \JFile::getExt($name);

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
}
