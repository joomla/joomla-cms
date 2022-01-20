<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Task.CheckFiles
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Path;

/**
 * Task plugin with routines that offer checks on files.
 * At the moment, offers a single routine to check and resize image files in a directory.
 *
 * @since  4.1.0
 */
class PlgTaskCheckfiles extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 *
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'checkfiles.imagesize' => [
			'langConstPrefix' => 'PLG_TASK_CHECK_FILES_TASK_IMAGE_SIZE',
			'form'            => 'image_size',
			'method'          => 'checkImages',
		],
	];

	/**
	 * @var boolean
	 * @since 4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @since 4.1.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'standardRoutineHandler',
			'onContentPrepareForm' => 'enhanceTaskItemForm',
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @since 4.1.0
	 * @throws RuntimeException
	 * @throws LogicException
	 */
	protected function checkImages(ExecuteTaskEvent $event): int
	{
		$params = $event->getArgument('params');

		$path      = Path::check(JPATH_ROOT . '/images/' . $params->path);
		$dimension = $params->dimension;
		$limit     = $params->limit;
		$numImages = max(1, (int) $params->numImages ?? 1);

		if (!Folder::exists($path))
		{
			$this->logTask(Text::_('PLG_TASK_CHECK_FILES_LOG_IMAGE_PATH_NA'), 'warning');

			return TaskStatus::NO_RUN;
		}

		$images = Folder::files($path, '^.*\.(jpg|jpeg|png|gif|webp)', 2, true);

		foreach ($images as $imageFilename)
		{
			$properties = Image::getImageFileProperties($imageFilename);
			$resize     = $properties->$dimension > $limit;

			if (!$resize)
			{
				continue;
			}

			$height = $properties->height;
			$width  = $properties->width;

			$newHeight = $dimension === 'height' ? $limit : $height * $limit / $width;
			$newWidth  = $dimension === 'width' ? $limit : $width * $limit / $height;

			$this->logTask(Text::sprintf('PLG_TASK_CHECK_FILES_LOG_RESIZING_IMAGE', $width, $height, $newWidth, $newHeight, $imageFilename));

			$image = new Image($imageFilename);

			try
			{
				$image->resize($newWidth, $newHeight, false);
			}
			catch (LogicException $e)
			{
				$this->logTask('PLG_TASK_CHECK_FILES_LOG_RESIZE_FAIL', 'error');
				$resizeFail = true;
			}

			if (!empty($resizeFail))
			{
				return TaskStatus::KNOCKOUT;
			}

			if (!$image->toFile($imageFilename, $properties->type))
			{
				$this->logTask('PLG_TASK_CHECK_FILES_LOG_IMAGE_SAVE_FAIL', 'error');
			}

			--$numImages;

			// We do a limited number of resize per execution
			if ($numImages == 0)
			{
				break;
			}
		}

		return TaskStatus::OK;
	}
}
