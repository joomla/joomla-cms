<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    Task.Requests
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** A task plugin with routines to make HTTP requests. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\File;

/**
 * The plugin class
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgTaskCheckfiles extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	protected const TASKS_MAP = [
		'checkfiles.imagesize' => [
			'langConstPrefix' => 'PLG_TASK_CHECK_FILES_TASK_IMAGE_SIZE',
			'form'            => 'image_size',
			'call'            => 'checkImages'
		]
	];

	/**
	 * Autoload the language file
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * An array of supported Form contexts
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $supportedFormContexts = [
		'com_scheduler.task'
	];

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'makeRequest',
			'onContentPrepareForm' => 'enhanceForm'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function makeRequest(ExecuteTaskEvent $event): void
	{
		if (!array_key_exists($event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->taskStart($event);
		$routineId = $event->getRoutineId();
		$exitCode = $this->{self::TASKS_MAP[$routineId]['call']}($event);
		$this->taskEnd($event, $exitCode);
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION
	 */
	public function enhanceForm(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		if ($context === 'com_scheduler.task')
		{
			$this->enhanceTaskItemForm($form, $data);
		}
	}

	/**
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return integer  The exit code
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	protected function checkImages(ExecuteTaskEvent $event): int
	{
		$params = $event->getArgument('params');

		$path = JPATH_ROOT . '/images/' . $params->path;
		$dimension = $params->dimension;
		$limit = $params->limit;

		if (!Folder::exists($path))
		{
			$this->addTaskLog('Image path does not exist!', 'warning');

			return TaskStatus::NO_RUN;
		}

		$images = Folder::files($path, '^.*\.(jpg|jpeg|png|gif)', 2, true);

		foreach ($images as $image)
		{
			$properties = Image::getImageFileProperties($image);
			$resize = $properties->$dimension > $limit;

			if (!$resize)
			{
				continue;
			}

			$height = $properties->height;
			$width = $properties->width;

			$this->addTaskLog("Found image size ${width}x${height}. Resizing " . $image);

			$newHeight = $dimension === 'height' ? $limit : $height * $limit / $width;
			$newWidth = $dimension === 'width' ? $limit : $width * $limit / $height;

			$imageFile = new Image($image);
			$type = File::getExt($image) === 'png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG;
			$imageFile->resize($newWidth, $newHeight)->toFile($image, $type);
			break;
		}

		return TaskStatus::OK;
	}
}
