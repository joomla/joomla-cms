<?php
/**
 * @package         Joomla.API
 * @subpackage      com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Component\Media\Administrator\Model\ApiModel;
use Joomla\Component\Media\Api\Helper\MediaHelper;

/**
 * Media web service model supporting a single media item.
 *
 * @since  4.0
 */
class MediumModel extends BaseModel
{
	/**
	 * Instance of com_media's ApiModel
	 *
	 * @var ApiModel
	 */
	private $mediaApiModel;

	public function __construct($config = [])
	{
		parent::__construct($config);

		$this->mediaApiModel = new ApiModel();
	}

	/**
	 * Method to get a single files or folder.
	 *
	 * @return  \stdClass  A file or folder object.
	 *
	 * @since   4.0.0
	 */
	public function getItem()
	{
		$options = [
			'path'    => $this->getState('path', ''),
			'url'     => $this->getState('url', false),
			'temp'    => $this->getState('temp', false),
			'content' => $this->getState('content', false)
		];

		list('adapter' => $adapterName, 'path' => $path) = MediaHelper::adapterNameAndPath($this->getState('path', ''));

		return $this->mediaApiModel->getFile($adapterName, $path, $options = []);
	}

	/**
	 * Method to save a file or folder.
	 *
	 * @param   string  $path  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function save($path = null)
	{
		$name     = $this->getState('name', '');
		$path     = $this->getState('path', '');
		$content  = $this->getState('content', null);
		$override = $this->getState('override', false);

		list('adapter' => $adapter, 'path' => $path) = MediaHelper::adapterNameAndPath($path);

		// If there is content, com_media's assumes the path refers to a file.
		// If not, a folder is assumed.
		if ($content)
		{
			// A file needs to be created
			$name = $this->mediaApiModel->createFile($adapter, $name, $path, $content, $override);
		}
		else
		{
			// A file needs to be created
			$name = $this->mediaApiModel->createFolder($adapter, $name, $path, $override);
		}

		return $path . '/' . $name;
	}

	/**
	 * Method to delete an existing file or folder.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete()
	{
		$path     = $this->getState('path', '');

		list('adapter' => $adapterName, 'path' => $path) = MediaHelper::adapterNameAndPath($path);

		$this->mediaApiModel->delete($adapterName, $path);
	}
}
