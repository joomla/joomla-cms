<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Api Media Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaControllerApi extends JControllerLegacy
{
	/**
	 * Api action endpoint for the media manager front end. The HTTP methods GET, PUT, POST and DELETE
	 * are supported.
	 *
	 * The following query parameters must be set to get a successfull response:
	 * - resource: 	The resource to work with, can be folders or files
	 * - name:		If a specific folder or file element is meant.
	 * - path:		The path when not the root element is meant.
	 *
	 * Some examples with a more understandable rest url equivalent:
	 * - GET a list of folders below the root:
	 * 		index.php?option=com_media&task=api.v1&resource=folders
	 * 		/api/v1/folders
	 * - GET a list of subfolders:
	 * 		index.php?option=com_media&task=api.v1&resource=folders&path=/sampledata/fruitshop
	 * 		/api/v1/folders/sampledata/fruitshop
	 * - POST a new folder into a specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=folders&path=/sampledata/fruitshop
	 * 		/api/v1/files/sampledata/fruitshop
	 * - DELETE an existing folder in a specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=folders&path=/sampledata/fruitshop/test
	 * 		/api/v1/files/sampledata/fruitshop/test
	 *
	 * - GET a list of files for specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=files&path=/sampledata/fruitshop
	 * 		/api/v1/files/sampledata/fruitshop
	 * - GET file information for a specific file:
	 * 		index.php?option=com_media&task=api.v1&resource=files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/v1/files/sampledata/fruitshop/test.jpg
	 * - POST a new file into a specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=files&path=/sampledata/fruitshop
	 * 		/api/v1/files/sampledata/fruitshop
	 * - PUT an existing file into a specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/v1/files/sampledata/fruitshop/test.jpg
	 * - DELETE an existing file in a specific folder:
	 * 		index.php?option=com_media&task=api.v1&resource=files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/v1/files/sampledata/fruitshop/test.jpg
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function v1()
	{
		// @todo add ACL check

		$method   = $this->input->getMethod() ? : 'GET';
		$resource = $this->input->get('resource');

		// Check if resource is known
		if ($resource != 'files' && $resource != 'folders')
		{
			return $this->send(null, 'Unknown resource!', true);
		}

		// Get the required variables
		$path = $this->input->getPath('path');

		// Buld the function name
		$functionName = strtolower($method) . ucfirst($resource);

		// Check if the function can be called
		if (!is_callable(array($this, $functionName)))
		{
			return $this->send(null, "Can't process request?", true);
		}

		// Gather the data
		$data = call_user_func(array($this, $functionName), $path, $this->input->json);

		// Return the data
		return $this->send($data, 'ok', false);
	}

	/**
	 * Returns the folders for the given folder. If the name is set,then it returns the folder
	 * meta data.
	 *
	 * @param  string      $path   The folder
	 * @param  JInputJSON  $input  The input
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getFolders($path = null, JInputJSON $input = null)
	{
		return array('Demo');
	}

	/**
	 * Echoes the given data as JSON in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":["Demo"]}
	 *
	 * @param mixed   $data     The data to send
	 * @param string  $message  The message
	 * @param boolean $success  If is is a successfull response
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function send($data, $message, $success)
	{
		echo new JResponseJson($data, $message, $success);

		JFactory::getApplication()->close();
	}
}
