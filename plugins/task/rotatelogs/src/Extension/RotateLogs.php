<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.rotatelogs
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\RotateLogs\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. Offers 1 task routines Rotate Logs
 * {@see ExecuteTaskEvent}.
 *
 * @since 5.0.0
 */
final class RotateLogs extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since 5.0.0
     */
    private const TASKS_MAP = [
        'rotation.logs' => [
            'langConstPrefix' => 'PLG_TASK_ROTATELOGS_ROTATION',
            'method'          => 'rotateLogs',
            'form'            => 'rotateForm',
        ],
    ];

    /**
     * @var boolean
     * @since 5.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 5.0.0
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
     * Method for the logs rotation task.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  5.0.0
     * @throws \Exception
     */
    private function rotateLogs(ExecuteTaskEvent $event): int
    {
        $logsToKeep = (int) $event->getArgument('params')->logstokeep ?? 1;

        // Get the log path
        $logPath = Path::clean($this->getApplication()->get('log_path'));

        // Invalid path, stop processing further
        if (!is_dir($logPath)) {
            return Status::KNOCKOUT;
        }

        $logFiles = $this->getLogFiles($logPath);

        // Sort log files by version number in reverse order
        krsort($logFiles, SORT_NUMERIC);

        foreach ($logFiles as $version => $files) {
            if ($version >= $logsToKeep) {
                // Delete files which have version greater than or equals $logsToKeep
                foreach ($files as $file) {
                    try {
                        File::delete($logPath . '/' . $file);
                    } catch (FilesystemException) {
                    }
                }
            } else {
                // For files which have version smaller than $logsToKeep, rotate (increase version number)
                foreach ($files as $file) {
                    $this->rotate($logPath, $file, $version);
                }
            }
        }

        return Status::OK;
    }

    /**
     * Method to rotate (increase version) of a log file
     *
     * @param   string  $path            Path to file to rotate
     * @param   string  $filename        Name of file to rotate
     * @param   int     $currentVersion  The current version number
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function rotate($path, $filename, $currentVersion)
    {
        if ($currentVersion === 0) {
            $rotatedFile = $path . '/1.' . $filename;
        } else {
            /*
             * Rotated log file has this filename format [VERSION].[FILENAME].php. To rotate it, we just need to explode
             * the filename into an array, increase value of first element (keep version) and implode it back to get the
             * rotated file name
             */
            $parts    = explode('.', $filename);
            $parts[0] = $currentVersion + 1;

            $rotatedFile = $path . '/' . implode('.', $parts);
        }

        try {
            File::move($path . '/' . $filename, $rotatedFile);
        } catch (FilesystemException) {
        }
    }

    /**
     * Get log files from log folder
     *
     * @param   string  $path  The folder to get log files
     *
     * @return  array   The log files in the given path grouped by version number (not rotated files have number 0)
     *
     * @since   5.0.0
     */
    private function getLogFiles($path)
    {
        $logFiles = [];
        $files    = Folder::files($path, '\.php$');

        foreach ($files as $file) {
            $parts = explode('.', $file);

            /*
             * Rotated log file has this filename format [VERSION].[FILENAME].php. So if $parts has at least 3 elements
             * and the first element is a number, we know that it's a rotated file and can get it's current version
             */
            if (\count($parts) >= 3 && is_numeric($parts[0])) {
                $version = (int) $parts[0];
            } else {
                $version = 0;
            }

            if (!isset($logFiles[$version])) {
                $logFiles[$version] = [];
            }

            $logFiles[$version][] = $file;
        }

        return $logFiles;
    }
}
