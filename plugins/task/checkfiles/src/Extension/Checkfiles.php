<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.CheckFiles
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Checkfiles\Extension;

use Joomla\CMS\Image\Image;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines that offer checks on files.
 * At the moment, offers a single routine to check and resize image files in a directory.
 *
 * @since  4.1.0
 */
final class Checkfiles extends CMSPlugin implements SubscriberInterface
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
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * The root directory path
     *
     * @var    string
     * @since  4.2.0
     */
    private $rootDirectory;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     * @param   string               $rootDirectory  The root directory to look for images
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, string $rootDirectory)
    {
        parent::__construct($dispatcher, $config);

        $this->rootDirectory = $rootDirectory;
    }

    /**
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event
     *
     * @return integer  The exit code
     *
     * @since 4.1.0
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function checkImages(ExecuteTaskEvent $event): int
    {
        $params    = $event->getArgument('params');
        $path      = Path::check($this->rootDirectory . $params->path);
        $dimension = $params->dimension;
        $limit     = $params->limit;
        $numImages = max(1, (int) $params->numImages ?? 1);

        if (!is_dir($path)) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_CHECK_FILES_LOG_IMAGE_PATH_NA'), 'warning');

            return TaskStatus::NO_RUN;
        }

        foreach (Folder::files($path, '^.*\.(jpg|jpeg|png|gif|webp)', 2, true) as $imageFilename) {
            $properties = Image::getImageFileProperties($imageFilename);
            $resize     = $properties->$dimension > $limit;

            if (!$resize) {
                continue;
            }

            $height = $properties->height;
            $width  = $properties->width;

            $newHeight = $dimension === 'height' ? $limit : $height * $limit / $width;
            $newWidth  = $dimension === 'width' ? $limit : $width * $limit / $height;

            $this->logTask(\sprintf(
                $this->getApplication()->getLanguage()->_('PLG_TASK_CHECK_FILES_LOG_RESIZING_IMAGE'),
                $width,
                $height,
                $newWidth,
                $newHeight,
                $imageFilename
            ));

            $image = new Image($imageFilename);

            try {
                $image->resize($newWidth, $newHeight, false);
            } catch (\LogicException $e) {
                $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_CHECK_FILES_LOG_RESIZE_FAIL'), 'error');

                return TaskStatus::KNOCKOUT;
            }

            if (!$image->toFile($imageFilename, $properties->type)) {
                $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_CHECK_FILES_LOG_IMAGE_SAVE_FAIL'), 'error');

                return TaskStatus::KNOCKOUT;
            }

            --$numImages;

            // We do a limited number of resize per execution
            if ($numImages == 0) {
                break;
            }
        }

        return TaskStatus::OK;
    }
}
