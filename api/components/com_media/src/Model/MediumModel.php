<?php
/**
 * @package         Joomla.API
 * @subpackage      com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see
 *                  LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Component\Media\Administrator\Model\ApiModel;
use Joomla\Component\Media\Api\Helper\MediaHelper;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * Media web service model supporting a single media item.
 *
 * @since  4.0
 */
class MediumModel extends BaseModel {

	/**
	 * Instance of com_media's ApiModel
	 *
	 * @var ApiModel
	 */
	private $mediaApiModel;

	public function __construct($config = []) {
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
	public function getItem() {
		$options = [
			'path'    => $this->getState('path', ''),
			'url'     => $this->getState('url', FALSE),
			'temp'    => $this->getState('temp', FALSE),
			'content' => $this->getState('content', FALSE),
		];

		[
			'adapter' => $adapterName,
			'path'    => $path,
		] = MediaHelper::adapterNameAndPath($this->getState('path', ''));

		return $this->mediaApiModel->getFile($adapterName, $path, $options);
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
	public function save($path = NULL) {
		$path     = $this->getState('path', '');
		$oldPath  = $this->getState('old_path', '');
		$content  = $this->getState('content', NULL);
		$override = $this->getState('override', FALSE);

		[
			'adapter' => $adapterName,
			'path'    => $path,
		] = MediaHelper::adapterNameAndPath($path);

		$resultPath = '';

		// If we have a (new) path and an old path, we want to move an existing
		// file or folder. This must be done before updating the content of a file,
		// if also requested (see below).
		if ($path && $oldPath)
		{
			// ApiModel::move() (or actually LocalAdapter::move()) returns a path
			// with leading slash.
			$resultPath = trim($this->mediaApiModel->move($adapterName, $oldPath, $path, $override), '/');
		}

		// If we have a (new) path but no old path, we want to create a
		// new file or folder.
		if ($path && !$oldPath)
		{
			// com_media expects separate directory and file name.
			// If we moved the file before, we must use the new path.
			$basename = basename($resultPath ?: $path);
			$dirname  = dirname($resultPath ?: $path);

			// If there is content, com_media's assumes the new item is a file.
			// Otherwise a folder is assumed.
			$name = $content
				? $this->mediaApiModel->createFile($adapterName, $basename, $dirname, $content, $override)
				: $this->mediaApiModel->createFolder($adapterName, $basename, $dirname, $override);

			$resultPath = $dirname . '/' . $name;
		}

		// If we have no (new) path but we do have an old path and we have content,
		// we want to update the contents of an existing file.
		if ($oldPath && $content)
		{
			// com_media expects separate directory and file name.
			// If we moved the file before, we must use the new path.
			$basename = basename($resultPath ?: $oldPath);
			$dirname  = dirname($resultPath ?: $oldPath);

			$this->mediaApiModel->updateFile($adapterName, $basename, $dirname, $content);

			$resultPath = $oldPath;
		}

		// If we still have no result path, something fishy is going on.
		if (!$resultPath)
		{
			throw new InvalidParameterException();
		}

		return $resultPath;
	}

	/**
	 * Method to delete an existing file or folder.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete() {
		$path = $this->getState('path', '');

		[
			'adapter' => $adapterName,
			'path'    => $path,
		] = MediaHelper::adapterNameAndPath($path);

		$this->mediaApiModel->delete($adapterName, $path);
	}

}
