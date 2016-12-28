<?php
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Api Media Controller
 *
 * This is NO public api controller, it is internal for the com_media component only!
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaControllerApi extends JControllerLegacy
{
	/**
	 * The local file adapter to work with.
	 *
	 * @var MediaFileAdapterInterface
	 */
	private $adapter = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.0
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
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 * - GET file information for a specific file:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * - POST a new file or folder into a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 *
	 * - PUT a media file:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 * - PUT process a media file:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg&action=process
	 * 		/api/files/sampledata/fruitshop/test.jpg/process
	 *
	 * - DELETE an existing folder in a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test
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
		// @todo add ACL check

		// Get the required variables
		$path = $this->input->getPath('path', '/');

		// Determine the method
		$method = $this->input->getMethod() ? : 'GET';

		try
		{
			// Gather the data accoring to the method
			switch (strtolower($method))
			{
				case 'get':
					$data = $this->adapter->getFiles($path);
					break;
				case 'delete':
					$data = $this->adapter->delete($path);
					break;
				case 'post':
				case 'put':
				default:
					throw new BadMethodCallException('Method not supported yet!');
			}


			// Return the data
			$this->sendAndClose($data, 'ok', false);
		}
		catch (Exception $e)
		{
			$this->sendAndClose(null, 'Error: ' . $e->getMessage(), true);
		}
	}

	/**
	 * Echoes the given data as JSON in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":[{"type":"dir","name":"banners","path":"//"}]}
	 *
	 * @param mixed    $data     The data to send
	 * @param string   $message  The message
	 * @param boolean  $error    If it is an error response
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function sendAndClose($data, $message, $error)
	{
		echo new JResponseJson($data, $message, $error);

		JFactory::getApplication()->close();
	}
}
