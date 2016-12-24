<?php
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
	 * Api endpoint for the media manager front end. The HTTP methods GET, PUT, POST and DELETE
	 * are supported.
	 *
	 * The following query parameters are processed:
	 * - path: The path of the resource, if not set then the default / is taken.
	 *
	 * Some examples with a more understandable rest url equivalent:
	 * - GET a list of folders below the root:
	 * 		index.php?option=com_media&task=api.folders
	 * 		/api/folders
	 * - GET a list of subfolders:
	 * 		index.php?option=com_media&task=api.folders&path=/sampledata/fruitshop
	 * 		/api/folders/sampledata/fruitshop
	 * - POST a new folder into a specific folder:
	 * 		index.php?option=com_media&task=api.folders&path=/sampledata/fruitshop
	 * 		/api/folders/sampledata/fruitshop
	 * - DELETE an existing folder in a specific folder:
	 * 		index.php?option=com_media&task=api.folders&path=/sampledata/fruitshop/test
	 * 		/api/folders/sampledata/fruitshop/test
	 *
	 * - GET a list of files for specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 * - GET file information for a specific file:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 * - POST a new file into a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop
	 * 		/api/files/sampledata/fruitshop
	 * - PUT an existing file into a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 * - DELETE an existing file in a specific folder:
	 * 		index.php?option=com_media&task=api.files&path=/sampledata/fruitshop/test.jpg
	 * 		/api/files/sampledata/fruitshop/test.jpg
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function folders()
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
					$data = $this->getFolders($path);
					break;
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
	private function getFolders($path = null)
	{
		return array('Demo');
	}

	/**
	 * Echoes the given data as JSON in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":["Demo"]}
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
