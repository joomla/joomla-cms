<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This file handles ajax calls from image editor.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerAjax extends JControllerBase
{
	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object echo the view
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		JSession::checkToken() or die('Invalid Token');

		jimport('joomla.utilities.utility');
		$app = $this->getApplication();
		$input = $app->input;
		$document = JFactory::getDocument();
		$viewName = $app->input->getWord('view', 'ajax');
		$viewFormat = $document->getType();
		$editing = $input->get('editing', '', 'STRING');
		$isOriginal = $input->get('isOriginal', '', 'INT');
		$operation = $input->get('operation', '', 'STRING');
		$this->model = new MediaModelSync;

		$path = pathinfo($editing);
		$session = JFactory::getSession();
		$token = $session->getToken();

		// Check if the current file is the original file in images folder or temp file in media/temp folder
		if ($isOriginal == 0)
		{
			$image = new JImage(COM_MEDIA_BASE . '/' . $editing);
		}
		else
		{
			$image = new JImage(JPATH_ROOT . '/media/media/tmp/' . $path['filename'] . $token . '.' . $path['extension']);
		}

		switch ($operation)
		{
			case 'rotateLeft':
				$newimage = $image->rotate(90);
				break;
			case 'rotateRight':
				$newimage = $image->rotate(-90);
				break;
			case 'flipVertical':
				$newimage = $image->flip('vertical');
				break;
			case 'flipHorizontal':
				$newimage = $image->flip('horizontal');
				break;
			case 'flipBoth':
				$newimage = $image->flip('both');
				break;
			case 'undo':
				$step = $input->get('step', '', 'INT');

				switch ($step)
				{
					case 1:
						$newimage = $image->rotate(-90);
						break;
					case 2:
						$newimage = $image->rotate(90);
						break;
					case 3:
						$newimage = $image->flip('vertical');
						break;
					case 4:
						$newimage = $image->flip('horizontal');
						break;
					case 5:
						$newimage = $image->flip('both');
						break;
				}
				break;
			case 'redo':
				$step = $input->get('step', '', 'INT');

				switch ($step)
				{
					case 1:
						$newimage = $image->rotate(90);
						break;
					case 2:
						$newimage = $image->rotate(-90);
						break;
					case 3:
						$newimage = $image->flip('vertical');
						break;
					case 4:
						$newimage = $image->flip('horizontal');
						break;
					case 5:
						$newimage = $image->flip('both');
						break;
				}
				break;
			case 'save':
				$image->toFile(COM_MEDIA_BASE . '/' . $editing);
				$newimage = $image;
				break;
			case 'crop':
				$x1 = $input->get('x1', '', 'INT');
				$y1 = $input->get('y1', '', 'INT');
				$w = $input->get('w', '', 'INT');
				$h = $input->get('h', '', 'INT');
				$newimage = $image->crop($w, $h, $x1, $y1, true);
				break;
			case 'resize':
				$imageWidth = $input->get('imageWidth', '', 'INT');
				$imageHeight = $input->get('imageHeight', '', 'INT');
				$newimage = $image->resize($imageWidth, $imageHeight, true);
				break;
		}

		switch ($operation)
		{
			case 'duplicate':
				$duplicateName = $input->get('duplicateName', '', 'STRING');
				$pathToDuplicate = COM_MEDIA_BASE . '/' . $path['dirname'] . '/' . $duplicateName . '.' . $path['extension'];

				if (file_exists($pathToDuplicate))
				{
					$input->set('duplicatePath', 'false');
				}
				else
				{
					$image->toFile($pathToDuplicate);
					$input->set('duplicatePath', $path['dirname'] . '/' . $duplicateName . '.' . $path['extension']);
					$pathToAdd =($path['dirname'] == '') || ($path['dirname'] == '.') ? '' : $path['dirname'] . '\\';
					$pathToAdd = $pathToAdd . $duplicateName . '.' . $path['extension'];
					$this->model->addImageFromUploading($pathToAdd);
				}
				break;
			default:
				$newimage->toFile(JPATH_ROOT . '/media/media/tmp/' . $path['filename'] . $token . '.' . $path['extension']);
				$input->set('newimage', $path['filename'] . $token . '.' . $path['extension']);
				$input->set('isOriginal', '1');
				break;
		}

		$app->input->set('view', $viewName);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

		$viewClass = 'MediaView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'MediaModel' . ucfirst($viewName);

		if (false === class_exists($modelClass))
		{
			$modelClass = 'MediaModelDefault';
		}

		$view = new $viewClass(new $modelClass, $paths);

		// Render our view.
		echo $view->render();

		return true;
	}
}
