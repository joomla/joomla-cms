<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Core\Domain;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Part;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use DateTime;
use Exception;
use Psr\Log\LogLevel;

/**
 * Backup finalization domain
 */
class Finalization extends Part
{
	/** @var array The finalisation actions we have to execute (FIFO queue) */
	private $action_queue = [];

	/** @var array Additional objects which will also handle finalisation tasks */
	private $action_handlers = [];

	/** @var string The current method, shifted from the action queye */
	private $current_method = '';

	/** @var array A list of all backup parts to process */
	private $backup_parts = [];

	/** @var int The backup part we are currently processing */
	private $backup_parts_index = -1;

	/** @var array Which remote files to remove */
	private $remote_files_killlist = null;

	/** @var int How many finalisation steps I have in total */
	private $steps_total = 0;

	/** @var int How many finalisation steps I have already done */
	private $steps_done = 0;

	/** @var int How many finalisation substeps I have in total */
	private $substeps_total = 0;

	/** @var int How many finalisation substeps I have already done */
	private $substeps_done = 0;

	/**
	 * Get the percentage of finalization steps done
	 *
	 * @return  float
	 */
	public function getProgress()
	{
		if ($this->steps_total <= 0)
		{
			return 0;
		}

		$overall = $this->steps_done / $this->steps_total;
		$local   = 0;

		if ($this->substeps_total > 0)
		{
			$local = $this->substeps_done / $this->substeps_total;
		}

		return $overall + ($local / $this->steps_total);
	}

	/**
	 * Used by additional handler classes to relay their step to us
	 *
	 * @param   string  $step  The current step
	 */
	public function relayStep($step)
	{
		$this->setStep($step);
	}

	/**
	 * Used by additional handler classes to relay their substep to us
	 *
	 * @param   string  $substep  The current sub-step
	 */
	public function relaySubstep($substep)
	{
		$this->setSubstep($substep);
	}

	/**
	 * Implements the abstract method
	 */
	protected function _prepare()
	{
		// Make sure the break flag is not set
		$configuration = Factory::getConfiguration();
		$configuration->get('volatile.breakflag', false);

		// Populate actions queue
		$this->action_queue = [
			'remove_temp_files',
			'update_statistics',
			'update_filesizes',
			'run_post_processing',
			'kickstart_post_processing',
			'apply_quotas',
			'apply_remote_quotas',
			'mail_administrators',
			'update_statistics',
			// Run it a second time to update the backup end time after post processing, emails, etc ;)
		];

		// Remove the Kickstart post-processing if we do not have the option set
		$uploadKickstart = $configuration->get('akeeba.advanced.uploadkickstart', 0);

		if (!$uploadKickstart)
		{
			unset ($this->action_queue['kickstart_post_processing']);
		}

		// Allow adding finalization action objects using the volatile.core.finalization.action_handlers array
		$customHandlers = $configuration->get('volatile.core.finalization.action_handlers', null);

		if (is_array($customHandlers) && !empty($customHandlers))
		{
			foreach ($customHandlers as $handler)
			{
				$this->action_handlers[] = $handler;
			}
		}

		// Do we have a custom action queue set in volatile.core.finalization.action_queue?
		$customQueue       = $configuration->get('volatile.core.finalization.action_queue', null);
		$customQueueBefore = $configuration->get('volatile.core.finalization.action_queue_before', null);

		if (is_array($customQueue) && !empty($customQueue))
		{
			Factory::getLog()->debug('Overriding finalization action queue');
			$this->action_queue = [];

			foreach ($customQueue as $action)
			{
				if (method_exists($this, $action))
				{
					$this->action_queue[] = $action;
				}
				else
				{
					foreach ($this->action_handlers as $handler)
					{
						if (method_exists($handler, $action))
						{
							$this->action_queue[] = $action;
							break;
						}
					}
				}
			}
		}

		if (is_array($customQueueBefore) && !empty($customQueueBefore))
		{
			// Get all actions before run_post_processing
			$temp   = [];
			$temp[] = array_shift($this->action_queue);
			$temp[] = array_shift($this->action_queue);
			$temp[] = array_shift($this->action_queue);

			// Add the custom handlers from volatile.core.finalization.action_handlers_before
			while (!empty($customQueueBefore))
			{
				$action = array_pop($customQueueBefore);

				if (method_exists($this, $action))
				{
					array_unshift($this->action_queue, $action);
				}
				else
				{
					foreach ($this->action_handlers as $handler)
					{
						if (method_exists($handler, $action))
						{
							array_unshift($this->action_queue, $action);

							break;
						}
					}
				}
			}

			// Add back the handlers we shifted before
			foreach ($temp as $action)
			{
				array_unshift($this->action_queue, $action);
			}
		}

		Factory::getLog()->debug('Finalization action queue: ' . implode(', ', $this->action_queue));

		$this->steps_total    = count($this->action_queue);
		$this->steps_done     = 0;
		$this->substeps_total = 0;
		$this->substeps_done  = 0;

		// Seed the method
		$this->current_method = array_shift($this->action_queue);

		// Set ourselves to running state
		$this->setState(self::STATE_RUNNING);
	}

	/**
	 * Implements the abstract method
	 *
	 * @return  void
	 */
	protected function _run()
	{
		$configuration = Factory::getConfiguration();

		if ($this->getState() == self::STATE_POSTRUN)
		{
			return;
		}

		$finished = (empty($this->action_queue)) && ($this->current_method == '');

		if ($finished)
		{
			$this->setState(self::STATE_POSTRUN);

			return;
		}

		$this->setState(self::STATE_RUNNING);

		$timer = Factory::getTimer();

		// Continue processing while we have still enough time and stuff to do
		while (($timer->getTimeLeft() > 0) && (!$finished) && (!$configuration->get('volatile.breakflag', false)))
		{
			$method = $this->current_method;

			if (method_exists($this, $method))
			{
				Factory::getLog()->debug(__CLASS__ . "::_run() Running built-in method $method");
				$status = $this->$method();
			}
			else
			{
				$status = true;

				if (!empty($this->action_handlers))
				{
					foreach ($this->action_handlers as $handler)
					{
						if (method_exists($handler, $method))
						{
							Factory::getLog()->debug(__CLASS__ . "::_run() Running add-on method $method");
							$status = $handler->$method($this);
							break;
						}
					}
				}
			}

			if ($status === true)
			{
				$this->current_method = '';
				$this->steps_done++;
				$finished = (empty($this->action_queue));

				if (!$finished)
				{
					$this->current_method = array_shift($this->action_queue);
					$this->substeps_total = 0;
					$this->substeps_done  = 0;
				}
			}
		}

		if ($finished)
		{
			$this->setState(self::STATE_POSTRUN);
			$this->setStep('');
			$this->setSubstep('');
		}
	}

	/**
	 * Implements the abstract method
	 *
	 * @return void
	 */
	protected function _finalize()
	{
		$this->setState(self::STATE_FINISHED);
	}

	/**
	 * Sends an email to the administrators
	 *
	 * @return bool True on success
	 */
	protected function mail_administrators()
	{
		$this->setStep('Processing emails to administrators');
		$this->setSubstep('');

		// Skip email for back-end backups
		if (Platform::getInstance()->get_backup_origin() == 'backend')
		{
			return true;
		}

		$emailFeatureEnabled = Platform::getInstance()->get_platform_configuration_option('frontend_email_on_finish', 0) != 0;

		/**
		 * Possible values:
		 * - always (default): email every time we reach this code
		 * - failedupload    : email only when the upload to remote storage has failed
		 */
		$emailWhen = Platform::getInstance()->get_platform_configuration_option('frontend_email_when', 'always');

		if (!$emailFeatureEnabled)
		{
			return true;
		}

		Factory::getLog()->debug("Preparing to send e-mail to administrators");

		$email = Platform::getInstance()->get_platform_configuration_option('frontend_email_address', '');
		$email = trim($email);

		if (!empty($email))
		{
			Factory::getLog()->debug("Using pre-defined list of emails");
			$emails = explode(',', $email);
		}
		else
		{
			Factory::getLog()->debug("Fetching list of Super Administrator emails");
			$emails = Platform::getInstance()->get_administrator_emails();
		}

		if (!empty($emails))
		{
			Factory::getLog()->debug("Creating email subject and body");
			// Fetch user's preferences
			$subject = trim(Platform::getInstance()->get_platform_configuration_option('frontend_email_subject', ''));
			$body    = trim(Platform::getInstance()->get_platform_configuration_option('frontend_email_body', ''));

			// Get the statistics
			$statistics = Factory::getStatistics();
			$stat       = $statistics->getRecord();
			$parts      = Factory::getStatistics()->get_all_filenames($stat, false);

			$profile_number = Platform::getInstance()->get_active_profile();
			$profile_name   = Platform::getInstance()->get_profile_name($profile_number);
			$parts          = Factory::getStatistics()->get_all_filenames($stat, false);
			$stat           = (object) $stat;
			$num_parts      = $stat->multipart;

			// Non-split archives have a part count of 0
			if ($num_parts == 0)
			{
				$num_parts = 1;
			}

			$parts_list = '';

			if (!empty($parts))
			{
				foreach ($parts as $file)
				{
					$parts_list .= "\t" . basename($file) . "\n";
				}
			}

			// Get the remote storage status
			$remote_status       = '';
			$post_proc_engine    = Factory::getConfiguration()->get('akeeba.advanced.postproc_engine');
			$failedUploadMessage = Platform::getInstance()->translate('COM_AKEEBA_EMAIL_POSTPROCESSING_FAILED');

			if (!empty($post_proc_engine) && ($post_proc_engine != 'none'))
			{
				if (empty($stat->remote_filename))
				{
					$remote_status = $failedUploadMessage;
				}
				else
				{
					$remote_status = Platform::getInstance()->translate('COM_AKEEBA_EMAIL_POSTPROCESSING_SUCCESS');
				}
			}

			// Did the user ask to be emailed only on failed uploads but the upload has succeeded?
			if (($emailWhen == 'failedupload') && ($remote_status != $failedUploadMessage))
			{
				return true;
			}

			// Do we need a default subject?
			if (empty($subject))
			{
				// Get the default subject
				$subject = Platform::getInstance()->translate('COM_AKEEBA_COMMON_EMAIL_SUBJECT_OK');
			}
			else
			{
				// Post-process the subject
				$subject = Factory::getFilesystemTools()->replace_archive_name_variables($subject);
			}

			// Do we need a default body?
			if (empty($body))
			{
				$body        = Platform::getInstance()->translate('COM_AKEEBA_COMMON_EMAIL_BODY_OK');
				$info_source = Platform::getInstance()->translate('COM_AKEEBA_COMMON_EMAIL_BODY_INFO');
				$body        .= "\n\n" . sprintf($info_source, $profile_number, $num_parts) . "\n\n";
				$body        .= $parts_list;
			}
			else
			{
				// Post-process the body
				$body = Factory::getFilesystemTools()->replace_archive_name_variables($body);
				$body = str_replace('[PROFILENUMBER]', $profile_number, $body);
				$body = str_replace('[PROFILENAME]', $profile_name, $body);
				$body = str_replace('[PARTCOUNT]', $num_parts, $body);
				$body = str_replace('[FILELIST]', $parts_list, $body);
				$body = str_replace('[REMOTESTATUS]', $remote_status, $body);
			}
			// Sometimes $body contains literal \n instead of newlines
			$body = str_replace('\\n', "\n", $body);

			foreach ($emails as $email)
			{
				Factory::getLog()->debug("Sending email to $email");
				Platform::getInstance()->send_email($email, $subject, $body);
			}
		}
		else
		{
			Factory::getLog()->debug("No email recipients found! Skipping email.");
		}

		return true;
	}

	/**
	 * Removes temporary files
	 *
	 * @return bool True on success
	 */
	protected function remove_temp_files()
	{
		$this->setStep('Removing temporary files');
		$this->setSubstep('');
		Factory::getLog()->debug("Removing temporary files");
		Factory::getTempFiles()->deleteTempFiles();

		return true;
	}

	/**
	 * Runs the writer's post-processing steps
	 *
	 * @return bool True on success
	 */
	protected function run_post_processing()
	{
		$this->setStep('Post-processing');

		// Do not run if the archive engine doesn't produce archives
		$configuration = Factory::getConfiguration();
		$this->setSubstep('');

		$engine_name = $configuration->get('akeeba.advanced.postproc_engine');

		Factory::getLog()->debug("Loading post-processing engine object ($engine_name)");

		$post_proc = Factory::getPostprocEngine($engine_name);

		// Initialize the archive part list if required
		if (empty($this->backup_parts))
		{
			Factory::getLog()->info('Initializing post-processing engine');

			// Initialize the flag for multistep post-processing of parts
			$configuration->set('volatile.postproc.filename', null);
			$configuration->set('volatile.postproc.directory', null);

			// Populate array w/ absolute names of backup parts
			$statistics         = Factory::getStatistics();
			$stat               = $statistics->getRecord();
			$this->backup_parts = Factory::getStatistics()->get_all_filenames($stat, false);

			if (is_null($this->backup_parts))
			{
				// No archive produced, or they are all already post-processed
				Factory::getLog()->info('No archive files found to post-process');

				return true;
			}

			Factory::getLog()->debug(count($this->backup_parts) . ' files to process found');

			$this->substeps_total = count($this->backup_parts);
			$this->substeps_done  = 0;

			$this->backup_parts_index = 0;

			// If we have an empty array, do not run
			if (empty($this->backup_parts))
			{
				return true;
			}

			// Break step before processing?
			if ($post_proc->recommendsBreakBefore() && !Factory::getConfiguration()
					->get('akeeba.tuning.nobreak.finalization', 0)
			)
			{
				Factory::getLog()->debug('Breaking step before post-processing run');
				$configuration->set('volatile.breakflag', true);

				return false;
			}
		}

		// Make sure we don't accidentally break the step when not required to do so
		$configuration->set('volatile.breakflag', false);

		// Do we have a filename from the previous run of the post-proc engine?
		$filename = $configuration->get('volatile.postproc.filename', null);

		if (empty($filename))
		{
			$filename = $this->backup_parts[$this->backup_parts_index];
			Factory::getLog()->info('Beginning post processing file ' . $filename);
		}
		else
		{
			Factory::getLog()->info('Continuing post processing file ' . $filename);
		}

		$this->setStep('Post-processing');
		$this->setSubstep(basename($filename));
		$timer               = Factory::getTimer();
		$startTime           = $timer->getRunningTime();
		$processingException = null;

		try
		{
			$finishedProcessing = $post_proc->processPart($filename);
		}
		catch (Exception $e)
		{
			$finishedProcessing  = false;
			$processingException = $e;
		}

		if (!is_null($processingException))
		{
			Factory::getLog()->warning('Failed to process file ' . $filename);
			Factory::getLog()->warning('Error received from the post-processing engine:');
			self::logErrorsFromException($processingException, LogLevel::WARNING);
		}
		elseif ($finishedProcessing === true)
		{
			// The post-processing of this file ended successfully
			Factory::getLog()->info('Finished post-processing file ' . $filename);
			$configuration->set('volatile.postproc.filename', null);
		}
		else
		{
			// More work required
			Factory::getLog()->info('More post-processing steps required for file ' . $filename);
			$configuration->set('volatile.postproc.filename', $filename);

			// Do we need to break the step?
			$endTime  = $timer->getRunningTime();
			$stepTime = $endTime - $startTime;
			$timeLeft = $timer->getTimeLeft();

			// By default, we assume that we have enough time to run yet another step
			$configuration->set('volatile.breakflag', false);

			/**
			 * However, if the last step took longer than the time we already have left on the timer we can predict
			 * that we are running out of time, therefore we need to break the step.
			 */
			if ($timeLeft < $stepTime)
			{
				$configuration->set('volatile.breakflag', true);
			}
		}

		// Should we delete the file afterwards?
		$canAndShouldDeleteFileAfterwards =
			$configuration->get('engine.postproc.common.delete_after', false)
			&& $post_proc->isFileDeletionAfterProcessingAdvisable();

		if ($canAndShouldDeleteFileAfterwards && $finishedProcessing)
		{
			Factory::getLog()->debug('Deleting already processed file ' . $filename);
			Platform::getInstance()->unlink($filename);
		}
		elseif ($canAndShouldDeleteFileAfterwards && !$finishedProcessing)
		{
			Factory::getLog()->debug('Not removing the non-processed file ' . $filename);
		}
		else
		{
			Factory::getLog()->debug('Not removing processed file ' . $filename);
		}

		if ($finishedProcessing === true)
		{
			// Move the index forward if the part finished processing
			$this->backup_parts_index++;

			// Mark substep done
			$this->substeps_done++;

			// Break step after processing?
			if ($post_proc->recommendsBreakAfter() && !Factory::getConfiguration()->get('akeeba.tuning.nobreak.finalization', 0))
			{
				$configuration->set('volatile.breakflag', true);
			}

			// If we just finished processing the first archive part, save its remote path in the statistics.
			if (($this->substeps_done == 1) || ($this->substeps_total == 0))
			{
				if (!empty($post_proc->getRemotePath()))
				{
					$statistics      = Factory::getStatistics();
					$remote_filename = $engine_name . '://';
					$remote_filename .= $post_proc->getRemotePath();
					$data            = [
						'remote_filename' => $remote_filename,
					];
					$remove_after    = $configuration->get('engine.postproc.common.delete_after', false);

					if ($remove_after)
					{
						$data['filesexist'] = 0;
					}

					$statistics->setStatistics($data);
				}
			}

			// Are we past the end of the array (i.e. we're finished)?
			if ($this->backup_parts_index >= count($this->backup_parts))
			{
				Factory::getLog()->info('Post-processing has finished for all files');

				return true;
			}
		}

		if (!is_null($processingException))
		{
			// If the post-processing failed, make sure we don't process anything else
			$this->backup_parts_index = count($this->backup_parts);
			Factory::getLog()->warning('Post-processing interrupted -- no more files will be transferred');

			return true;
		}

		// Indicate we're not done yet
		return false;
	}

	/**
	 * Runs the Kickstart post-processing step
	 *
	 * @return  bool True on success
	 */
	protected function kickstart_post_processing()
	{
		$this->setStep('Post-processing Kickstart');

		$configuration = Factory::getConfiguration();
		$this->setSubstep('');

		// Do not run if we are not told to upload Kickstart
		$uploadKickstart = $configuration->get('akeeba.advanced.uploadkickstart', 0);

		if (!$uploadKickstart)
		{
			Factory::getLog()->info("Getting ready to upload Kickstart");

			return true;
		}

		$engine_name = $configuration->get('akeeba.advanced.postproc_engine');
		Factory::getLog()->debug("Loading post-processing engine object ($engine_name)");
		$post_proc = Factory::getPostprocEngine($engine_name);

		// Set $filename to kickstart's source file
		$filename = Platform::getInstance()->get_installer_images_path() . '/kickstart.txt';

		// Post-process the file
		$this->setSubstep('kickstart.php');

		if (!@file_exists($filename) || !is_file($filename))
		{
			Factory::getLog()->warning('Failed to upload kickstart.php. Missing file ' . $filename);

			// Indicate we're done.
			return true;
		}

		$exception          = null;
		$finishedProcessing = false;

		try
		{
			$finishedProcessing = $post_proc->processPart($filename, 'kickstart.php');
		}
		catch (Exception $e)
		{
			$exception = $e;
		}

		if (!is_null($exception))
		{
			Factory::getLog()->warning('Failed to upload kickstart.php');
			Factory::getLog()->warning('Error received from the post-processing engine:');
			self::logErrorsFromException($exception, LogLevel::WARNING);
		}
		elseif ($finishedProcessing === true)
		{
			// The post-processing of this file ended successfully
			Factory::getLog()->info('Finished uploading kickstart.php');
			$configuration->set('volatile.postproc.filename', null);
		}

		// Indicate we're done
		return true;
	}

	/**
	 * Updates the backup statistics record
	 *
	 * @return bool True on success
	 */
	protected function update_statistics()
	{
		$this->setStep('Updating backup record information');
		$this->setSubstep('');

		Factory::getLog()->debug("Updating statistics");
		// We finished normally. Fetch the stats record
		$statistics = Factory::getStatistics();
		$registry   = Factory::getConfiguration();
		$data       = [
			'backupend' => Platform::getInstance()->get_timestamp_database(),
			'status'    => 'complete',
			'multipart' => $registry->get('volatile.statistics.multipart', 0),
		];

		try
		{
			$result = $statistics->setStatistics($data);
		}
		catch (Exception $e)
		{
			$result = false;
		}

		if ($result === false)
		{
			// Most likely a "MySQL has gone away" issue...
			$configuration = Factory::getConfiguration();
			$configuration->set('volatile.breakflag', true);

			return false;
		}

		$stat = (object) $statistics->getRecord();
		Platform::getInstance()->remove_duplicate_backup_records($stat->archivename);

		return true;
	}

	protected function update_filesizes()
	{
		$this->setStep('Updating file sizes');
		$this->setSubstep('');
		Factory::getLog()->debug("Updating statistics with file sizes");

		// Fetch the stats record
		$statistics = Factory::getStatistics();
		$record     = $statistics->getRecord();
		$filenames  = $statistics->get_all_filenames($record);
		$filesize   = 0.0;

		// Calculate file sizes of files remaining on the server
		if (!empty($filenames))
		{
			foreach ($filenames as $file)
			{
				$size = @filesize($file);

				if ($size !== false)
				{
					$filesize += $size * 1.0;
				}
			}
		}

		// Get the part size in volatile storage, set from the immediate part uploading effected by the
		// "Process each part immediately" option, and add it to the total file size
		$config              = Factory::getConfiguration();
		$postProcImmediately = $config->get('engine.postproc.common.after_part', 0, false);
		$deleteAfter         = $config->get('engine.postproc.common.delete_after', 0, false);
		$postProcEngine      = $config->get('akeeba.advanced.postproc_engine', 'none');

		if ($postProcImmediately && $deleteAfter && ($postProcEngine != 'none'))
		{
			$volatileTotalSize = Factory::getConfiguration()->get('volatile.engine.archiver.totalsize', 0);

			if ($volatileTotalSize)
			{
				$filesize += $volatileTotalSize;
			}
		}

		$data = [
			'total_size' => $filesize,
		];

		Factory::getLog()->debug("Total size of backup archive (in bytes): $filesize");

		$statistics->setStatistics($data);

		return true;
	}

	/**
	 * Applies the size and count quotas
	 *
	 * @return bool True on success
	 *
	 * @throws Exception
	 */
	protected function apply_quotas()
	{
		$this->setStep('Applying quotas');
		$this->setSubstep('');

		// If no quota settings are enabled, quit
		$registry       = Factory::getConfiguration();
		$useDayQuotas   = $registry->get('akeeba.quota.maxage.enable');
		$useCountQuotas = $registry->get('akeeba.quota.enable_count_quota');
		$useSizeQuotas  = $registry->get('akeeba.quota.enable_size_quota');

		if (!($useDayQuotas || $useCountQuotas || $useSizeQuotas))
		{
			$this->apply_obsolete_quotas();

			Factory::getLog()->debug("No quotas were defined; old backup files will be kept intact");

			return true; // No quota limits were requested
		}

		// Try to find the files to be deleted due to quota settings
		$statistics     = Factory::getStatistics();
		$latestBackupId = $statistics->getId();

		// Get quota values
		$countQuota  = $registry->get('akeeba.quota.count_quota');
		$sizeQuota   = $registry->get('akeeba.quota.size_quota');
		$daysQuota   = $registry->get('akeeba.quota.maxage.maxdays');
		$preserveDay = $registry->get('akeeba.quota.maxage.keepday');

		// Get valid-looking backup ID's
		$validIDs = Platform::getInstance()->get_valid_backup_records(true);

		// Create a list of valid files
		$allFiles = [];

		if (count($validIDs))
		{
			foreach ($validIDs as $id)
			{
				$stat = Platform::getInstance()->get_statistics($id);

				// Exclude frozen record from quota management
				if (isset($stat['frozen']) && $stat['frozen'])
				{
					Factory::getLog()->debug(sprintf("Excluding frozen backup id %d from quota management", $id));
					continue;
				}

				try
				{
					$backupstart = new DateTime($stat['backupstart']);
					$backupTS    = $backupstart->format('U');
					$backupDay   = $backupstart->format('d');
				}
				catch (Exception $e)
				{
					$backupTS  = 0;
					$backupDay = 0;
				}

				// Get the log file name
				$tag      = $stat['tag'];
				$backupId = $stat['backupid'] ?? '';
				$logName  = '';

				if (!empty($backupId))
				{
					$logName = 'akeeba.' . $tag . '.' . $backupId . '.log.php';
				}

				// Multipart processing
				$filenames = Factory::getStatistics()->get_all_filenames($stat, true);

				if (!is_null($filenames))
				{
					// Only process existing files
					$filesize = 0;

					foreach ($filenames as $filename)
					{
						$filesize += @filesize($filename);
					}

					$allFiles[] = [
						'id'          => $id,
						'filenames'   => $filenames,
						'size'        => $filesize,
						'backupstart' => $backupTS,
						'day'         => $backupDay,
						'logname'     => $logName,
					];
				}
			}
		}

		unset($validIDs);

		// If there are no files, exit early
		if (count($allFiles) == 0)
		{
			Factory::getLog()->debug("There were no old backup files to apply quotas on");

			return true;
		}

		// Init arrays
		$killids  = [];
		$killLogs = [];
		$ret      = [];
		$leftover = [];

		// Do we need to apply maximum backup age quotas?
		if ($useDayQuotas)
		{
			$killDatetime = new DateTime();
			$killDatetime->modify('-' . $daysQuota . ($daysQuota == 1 ? ' day' : ' days'));
			$killTS = $killDatetime->format('U');

			foreach ($allFiles as $file)
			{
				if ($file['id'] == $latestBackupId)
				{
					continue;
				}

				// Is this on a preserve day?
				if ($preserveDay > 0)
				{
					if ($preserveDay == $file['day'])
					{
						$leftover[] = $file;
						continue;
					}
				}

				// Otherwise, check the timestamp
				if ($file['backupstart'] < $killTS)
				{
					$ret[]     = $file['filenames'];
					$killids[] = $file['id'];

					if (!empty($file['logname']))
					{
						$filePath = reset($file['filenames']);

						if (!empty($filePath))
						{
							if (@file_exists(dirname($filePath) . '/' . $file['logname']))
							{
								$killLogs[] = dirname($filePath) . '/' . $file['logname'];
							}
							elseif (@file_exists(dirname($filePath) . '/' . substr($file['logname'], 0, -4)))
							{
								/**
								 * Transitional period: the log file akeeba.tag.log.php may not exist but the
								 * akeeba.tag.log does. This addresses this transition.
								 */
								$killLogs[] = dirname($filePath) . '/' . substr($file['logname'], 0, -4);
							}

						}
					}
				}
				else
				{
					$leftover[] = $file;
				}
			}
		}

		// Do we need to apply count quotas?
		if ($useCountQuotas && is_numeric($countQuota) && !($countQuota <= 0) && !$useDayQuotas)
		{
			// Are there more files than the quota limit?
			if (!(count($allFiles) > $countQuota))
			{
				// No, effectively skip the quota checking
				$leftover = $allFiles;
			}
			else
			{
				Factory::getLog()->debug("Processing count quotas");
				// Yes, apply the quota setting. Add to $ret all entries minus the last
				// $countQuota ones.
				$totalRecords = count($allFiles);
				$checkLimit   = $totalRecords - $countQuota;

				// Only process if at least one file (current backup!) is to be left
				for ($count = 0; $count < $totalRecords; $count++)
				{
					$def = array_pop($allFiles);

					if ($def['id'] == $latestBackupId)
					{
						array_push($allFiles, $def);
						continue;
					}
					if (count($ret) < $checkLimit)
					{
						if ($latestBackupId != $def['id'])
						{
							$ret[]     = $def['filenames'];
							$killids[] = $def['id'];

							if (!empty($def['logname']))
							{
								$filePath = reset($def['filenames']);

								if (!empty($filePath))
								{
									if (@file_exists(dirname($filePath) . '/' . $def['logname']))
									{
										$killLogs[] = dirname($filePath) . '/' . $def['logname'];

									}
									elseif (@file_exists(dirname($filePath) . '/' . substr($def['logname'], 0, -4)))
									{
										/**
										 * Transitional period: the log file akeeba.tag.log.php may not exist but the
										 * akeeba.tag.log does. This addresses this transition.
										 */
										$killLogs[] = dirname($filePath) . '/' . substr($def['logname'], 0, -4);
									}
								}
							}
						}
					}
					else
					{
						$leftover[] = $def;
					}
				}
				unset($allFiles);
			}
		}
		else
		{
			// No count quotas are applied
			$leftover = $allFiles;
		}

		// Do we need to apply size quotas?
		if ($useSizeQuotas && is_numeric($sizeQuota) && !($sizeQuota <= 0) && (count($leftover) > 0) && !$useDayQuotas)
		{
			Factory::getLog()->debug("Processing size quotas");
			// OK, let's start counting bytes!
			$runningSize = 0;

			while (count($leftover) > 0)
			{
				// Each time, remove the last element of the backup array and calculate
				// running size. If it's over the limit, add the archive to the return array.
				$def         = array_pop($leftover);
				$runningSize += $def['size'];

				if ($runningSize >= $sizeQuota)
				{
					if ($latestBackupId == $def['id'])
					{
						$runningSize -= $def['size'];
					}
					else
					{
						$ret[]     = $def['filenames'];
						$killids[] = $def['id'];

						if (!empty($def['logname']))
						{
							$filePath = reset($def['filenames']);

							if (!empty($filePath))
							{
								$killLogs[] = dirname($filePath) . '/' . $def['logname'];
							}
						}
					}
				}
			}
		}

		// Convert the $ret 2-dimensional array to single dimensional
		$quotaFiles = [];

		foreach ($ret as $temp)
		{
			foreach ($temp as $filename)
			{
				$quotaFiles[] = $filename;
			}
		}

		// Update the statistics record with the removed remote files
		if (!empty($killids))
		{
			foreach ($killids as $id)
			{
				$data = ['filesexist' => '0'];
				Platform::getInstance()->set_or_update_statistics($id, $data);
			}
		}

		// Apply quotas to backup archives
		if (count($quotaFiles) > 0)
		{
			Factory::getLog()->debug("Applying quotas");

			foreach ($quotaFiles as $file)
			{
				if (!@Platform::getInstance()->unlink($file))
				{
					Factory::getLog()->warning("Failed to remove old backup file " . $file);
				}
			}
		}

		// Apply quotas to log files
		if (!empty($killLogs))
		{
			Factory::getLog()->debug("Removing obsolete log files");

			foreach ($killLogs as $logPath)
			{
				@Platform::getInstance()->unlink($logPath);
			}
		}

		$this->apply_obsolete_quotas();

		return true;
	}

	/**
	 * Apply quotas for remotely stored files
	 *
	 * @return bool True on success
	 */
	protected function apply_remote_quotas()
	{
		$this->setStep('Applying remote storage quotas');
		$this->setSubstep('');
		// Make sure we are enabled
		$config       = Factory::getConfiguration();
		$enableRemote = $config->get('akeeba.quota.remote', 0);

		if (!$enableRemote)
		{
			return true;
		}

		// Get the list of files to kill
		if (empty($this->remote_files_killlist))
		{
			Factory::getLog()->debug('Applying remote file quotas');
			$this->remote_files_killlist = $this->get_remote_quotas();

			if (empty($this->remote_files_killlist))
			{
				Factory::getLog()->debug('No remote files to apply quotas to were found');

				return true;
			}
		}

		// Remove the files
		$timer = Factory::getTimer();

		while ($timer->getRunningTime() && count($this->remote_files_killlist))
		{
			$filename = array_shift($this->remote_files_killlist);

			[$engineName, $path] = explode('://', $filename);

			$engine = Factory::getPostprocEngine($engineName);

			if (!$engine->supportsDelete())
			{
				continue;
			}

			Factory::getLog()->debug("Removing $filename");

			try
			{
				$engine->delete($path);
			}
			catch (Exception $e)
			{
				Factory::getLog()->debug("Removal failed: " . $e->getMessage());

				$result = false;
			}

		}

		// Return false if we have more work to do or true if we're done
		if (count($this->remote_files_killlist))
		{
			Factory::getLog()->debug("Remote file removal will continue in the next step");

			return false;
		}
		else
		{
			Factory::getLog()->debug("Remote file quotas applied successfully");

			$this->apply_obsolete_quotas();

			return true;
		}
	}

	/**
	 * Applies the size and count quotas
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	protected function get_remote_quotas()
	{
		// Get all records with a remote filename
		$allRecords = Platform::getInstance()->get_valid_remote_records();

		// Bail out if no records found
		if (empty($allRecords))
		{
			return [];
		}

		// Try to find the files to be deleted due to quota settings
		$statistics     = Factory::getStatistics();
		$latestBackupId = $statistics->getId();

		// Filter out the current record
		$temp = [];

		foreach ($allRecords as $item)
		{
			if ($item['id'] == $latestBackupId)
			{
				continue;
			}

			// Skip frozen records
			if (isset($item['frozen']) && $item['frozen'])
			{
				continue;
			}

			$item['files'] = $this->get_remote_files($item['remote_filename'], $item['multipart']);
			$temp[]        = $item;
		}

		$allRecords = $temp;

		// Bail out if only the current backup was included in the list
		if (count($allRecords) == 0)
		{
			return [];
		}

		// Get quota values
		$registry       = Factory::getConfiguration();
		$countQuota     = $registry->get('akeeba.quota.count_quota');
		$sizeQuota      = $registry->get('akeeba.quota.size_quota');
		$useCountQuotas = $registry->get('akeeba.quota.enable_count_quota');
		$useSizeQuotas  = $registry->get('akeeba.quota.enable_size_quota');
		$useDayQuotas   = $registry->get('akeeba.quota.maxage.enable');
		$daysQuota      = $registry->get('akeeba.quota.maxage.maxdays');
		$preserveDay    = $registry->get('akeeba.quota.maxage.keepday');

		$leftover = [];
		$ret      = [];
		$killids  = [];

		if ($useDayQuotas)
		{
			$killDatetime = new DateTime();
			$killDatetime->modify('-' . $daysQuota . ($daysQuota == 1 ? ' day' : ' days'));
			$killTS = $killDatetime->format('U');

			foreach ($allRecords as $def)
			{
				$backupstart = new DateTime($def['backupstart']);
				$backupTS    = $backupstart->format('U');
				$backupDay   = $backupstart->format('d');

				// Is this on a preserve day?
				if ($preserveDay > 0)
				{
					if ($preserveDay == $backupDay)
					{
						$leftover[] = $def;
						continue;
					}
				}

				// Otherwise, check the timestamp
				if ($backupTS < $killTS)
				{
					$ret[]     = $def['files'];
					$killids[] = $def['id'];
				}
				else
				{
					$leftover[] = $def;
				}
			}
		}

		// Do we need to apply count quotas?
		if ($useCountQuotas && ($countQuota >= 1) && !$useDayQuotas)
		{
			$countQuota--;
			// Are there more files than the quota limit?
			if (!(count($allRecords) > $countQuota))
			{
				// No, effectively skip the quota checking
				$leftover = $allRecords;
			}
			else
			{
				Factory::getLog()->debug("Processing remote count quotas");
				// Yes, apply the quota setting.
				$totalRecords = count($allRecords);

				for ($count = 0; $count <= $totalRecords; $count++)
				{
					$def = array_pop($allRecords);

					if (count($leftover) >= $countQuota)
					{
						$ret[]     = $def['files'];
						$killids[] = $def['id'];
					}
					else
					{
						$leftover[] = $def;
					}
				}

				unset($allRecords);
			}
		}
		else
		{
			// No count quotas are applied
			$leftover = $allRecords;
		}

		// Do we need to apply size quotas?
		if ($useSizeQuotas && ($sizeQuota > 0) && (count($leftover) > 0) && !$useDayQuotas)
		{
			Factory::getLog()->debug("Processing remote size quotas");
			// OK, let's start counting bytes!
			$runningSize = 0;

			while (count($leftover) > 0)
			{
				// Each time, remove the last element of the backup array and calculate
				// running size. If it's over the limit, add the archive to the $ret array.
				$def         = array_pop($leftover);
				$runningSize += $def['total_size'];

				if ($runningSize >= $sizeQuota)
				{
					$ret[]     = $def['files'];
					$killids[] = $def['id'];
				}
			}
		}

		// Convert the $ret 2-dimensional array to single dimensional
		$quotaFiles = [];

		foreach ($ret as $temp)
		{
			if (!is_array($temp) || empty($temp))
			{
				continue;
			}

			foreach ($temp as $filename)
			{
				$quotaFiles[] = $filename;
			}
		}

		// Update the statistics record with the removed remote files
		if (!empty($killids))
		{
			foreach ($killids as $id)
			{
				if (empty($id))
				{
					continue;
				}

				$data = ['remote_filename' => ''];
				Platform::getInstance()->set_or_update_statistics($id, $data);
			}
		}

		return $quotaFiles;
	}

	/**
	 * Get the full paths to all remote backup parts
	 *
	 * @param   string  $filename   The full filename of the last part stored in the database
	 * @param   int     $multipart  How many parts does this archive consist of?
	 *
	 * @return array A list of the full paths of all remotely stored backup archive parts
	 */
	protected function get_remote_files($filename, $multipart)
	{
		$result = [];

		$extension = substr($filename, -3);
		$base      = substr($filename, 0, -4);

		$result[] = $filename;

		if ($multipart > 1)
		{
			for ($i = 1; $i < $multipart; $i++)
			{
				$newExt   = substr($extension, 0, 1) . sprintf('%02u', $i);
				$result[] = $base . '.' . $newExt;
			}
		}

		return $result;
	}

	/**
	 * Keeps a maximum number of "obsolete" records
	 *
	 * @return  void
	 */
	protected function apply_obsolete_quotas()
	{
		$this->setStep('Applying quota limit on obsolete backup records');
		$this->setSubstep('');
		$registry = Factory::getConfiguration();
		$limit    = $registry->get('akeeba.quota.obsolete_quota', 0);
		$limit    = (int) $limit;

		if ($limit <= 0)
		{
			return;
		}

		$statsTable = Platform::getInstance()->tableNameStats;
		$db         = Factory::getDatabase(Platform::getInstance()->get_platform_database_options());
		$query      = $db->getQuery(true)
			->select([
				$db->qn('id'),
				$db->qn('tag'),
				$db->qn('backupid'),
				$db->qn('absolute_path'),
			])
			->from($db->qn($statsTable))
			->where($db->qn('profile_id') . ' = ' . $db->q(Platform::getInstance()->get_active_profile()))
			->where($db->qn('status') . ' = ' . $db->q('complete'))
			->where($db->qn('filesexist') . '=' . $db->q('0'))
			->where(
				'(' .
				$db->qn('remote_filename') . '=' . $db->q('') . ' OR ' .
				$db->qn('remote_filename') . ' IS NULL'
				. ')'
			)
			->order($db->qn('id') . ' DESC');

		$db->setQuery($query, $limit, 100000);
		$records = $db->loadAssocList();

		if (empty($records))
		{
			return;
		}

		$array = [];

		// Delete backup-specific log files if they exist and add the IDs of the records to delete in the $array
		foreach ($records as $stat)
		{
			$array[] = $stat['id'];

			// We can't delete logs if there is no backup ID in the record
			if (!isset($stat['backupid']) || empty($stat['backupid']))
			{
				continue;
			}

			$logFileName = 'akeeba.' . $stat['tag'] . '.' . $stat['backupid'] . '.log.php';
			$logPath     = dirname($stat['absolute_path']) . '/' . $logFileName;

			if (@file_exists($logPath))
			{
				@unlink($logPath);
			}

			/**
			 * Transitional period: the log file akeeba.tag.log.php may not exist but the akeeba.tag.log does. This
			 * addresses this transition.
			 */
			$logPath = dirname($stat['absolute_path']) . '/' . substr($logFileName, 0, -4);

			if (@file_exists($logPath))
			{
				@unlink($logPath);
			}
		}

		$ids = [];

		foreach ($array as $id)
		{
			$ids[] = $db->q($id);
		}

		$ids = implode(',', $ids);

		$query = $db->getQuery(true)
			->delete($db->qn($statsTable))
			->where($db->qn('id') . " IN ($ids)");
		$db->setQuery($query);
		$db->query();
	}
}
