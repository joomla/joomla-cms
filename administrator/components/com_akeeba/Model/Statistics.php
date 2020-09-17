<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Exceptions\FrozenRecordError;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Exception;
use FOF30\Container\Container;
use FOF30\Date\Date;
use FOF30\Model\DataModel\Exception\RecordNotLoaded;
use FOF30\Model\Model;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\User\User;
use RuntimeException;

class Statistics extends Model
{
	/**
	 * The JPagination object, used in the GUI
	 *
	 * @var  Pagination
	 */
	private $pagination;

	/**
	 * Public constructor.
	 *
	 * @param   Container  $container  The configuration variables to this model
	 * @param   array      $config     Configuration values for this model
	 */
	public function __construct(Container $container, array $config)
	{
		$defaultConfig = [
			'tableName'   => '#__ak_stats',
			'idFieldName' => 'id',
		];

		if (!is_array($config) || empty($config))
		{
			$config = [];
		}

		$config = array_merge($defaultConfig, $config);

		parent::__construct($container, $config);

		$platform     = $this->container->platform;
		$defaultLimit = $platform->getConfig()->get('list_limit', 10);

		if ($platform->isCli())
		{
			$limit      = $this->input->getInt('limit', $defaultLimit);
			$limitstart = $this->input->getInt('limitstart', 0);
		}
		else
		{
			$limit      = $platform->getUserStateFromRequest('global.list.limit', 'limit', $this->input, $defaultLimit);
			$limitstart = $platform->getUserStateFromRequest('com_akeeba.stats.limitstart', 'limitstart', $this->input, 0);
		}

		if ($platform->isFrontend())
		{
			$limit      = 0;
			$limitstart = 0;
		}

		// Set the page pagination variables
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Returns the same list as getStatisticsList(), but includes an extra field
	 * named 'meta' which categorises attempts based on their backup archive status
	 *
	 * @param   bool   $overrideLimits  Should I disregard limit, limitStart and filters?
	 * @param   array  $filters         Filters to apply. See Platform::get_statistics_list
	 * @param   array  $order           Results ordering. The accepted keys are by (column name) and order (ASC or DESC)
	 *
	 * @return  array  An array of arrays. Each inner array is one backup record.
	 */
	public function &getStatisticsListWithMeta($overrideLimits = false, $filters = null, $order = null)
	{
		$limitstart = $this->getState('limitstart', 0);
		$limit      = $this->getState('limit', 10);

		if ($overrideLimits)
		{
			$limitstart = 0;
			$limit      = 0;
			$filters    = null;
		}

		if (is_array($order) && isset($order['order']))
		{
			if (strtoupper($order['order']) != 'ASC')
			{
				$order['order'] = 'desc';
			}
		}

		$allStats = Platform::getInstance()->get_statistics_list([
			'limitstart' => $limitstart,
			'limit'      => $limit,
			'filters'    => $filters,
			'order'      => $order,
		]);

		$validRecords = Platform::getInstance()->get_valid_backup_records();

		if (empty($validRecords))
		{
			$validRecords = [];
		}

		// This will hold the entries whose files are no longer present and are
		// not already marked as such in the database
		$updateObsoleteRecords = [];

		// The list of statistics entries to return
		$ret = [];

		if (empty($allStats))
		{
			return $ret;
		}

		foreach ($allStats as $stat)
		{
			// Translate backup status and the existence of a remote filename to the backup record's "meta" status.
			switch ($stat['status'])
			{
				case 'run':
					$stat['meta'] = 'pending';
					break;

				case 'fail':
					$stat['meta'] = 'fail';
					break;

				default:
					if ($stat['remote_filename'])
					{
						// If there is a "remote_filename", the record is "remote", not "obsolete"
						$stat['meta'] = 'remote';
					}
					else
					{
						// Else, it's "obsolete"
						$stat['meta'] = 'obsolete';
					}
					break;
			}

			// If the backup is reported to have files still stored on the server we need to investigate further
			if (in_array($stat['id'], $validRecords))
			{
				$archives     = Factory::getStatistics()->get_all_filenames($stat);
				$count        = is_array($archives) ? count($archives) : 0;
				$stat['meta'] = ($count > 0) ? 'ok' : 'obsolete';

				// The archives exist. Set $stat['size'] to the total size of the backup archives.
				if ($stat['meta'] == 'ok')
				{
					$stat['size'] = $stat['total_size'];

					if ($stat['total_size'] <= 0)
					{
						$stat['size'] = 0;

						foreach ($archives as $filename)
						{
							$stat['size'] += @filesize($filename);
						}
					}

					$ret[] = $stat;

					continue;
				}

				// The archives do not exist or we can't find them. If the record says otherwise we need to update it.
				if ($stat['filesexist'])
				{
					$updateObsoleteRecords[] = $stat['id'];
				}

				// Does the backup record report a total size even though our files no longer exist?
				if ($stat['total_size'])
				{
					$stat['size'] = $stat['total_size'];
				}

				// If there is a "remote_filename", the record is "remote", not "obsolete"
				if ($stat['remote_filename'])
				{
					$stat['meta'] = 'remote';
				}
			}

			$ret[] = $stat;
		}

		// Update records which report that their files exist on the server but, in fact, they don't.
		if (count($updateObsoleteRecords))
		{
			Platform::getInstance()->invalidate_backup_records($updateObsoleteRecords);
		}

		unset($validRecords);

		return $ret;
	}

	/**
	 * Send an email notification for failed backups
	 *
	 * @return  array  See the CLI script
	 */
	public function notifyFailed()
	{
		// Invalidate stale backups
		try
		{
			Factory::resetState([
				'global' => true,
				'log'    => false,
				'maxrun' => $this->container->params->get('failure_timeout', 180),
			]);
		}
		catch (Exception $e)
		{
			// This will die if the output directory is invalid. Let it die, then.
		}

		// Get the last execution and search for failed backups AFTER that date
		$last = $this->getLastCheck();

		// Get failed backups
		$filters = [
			['field' => 'status', 'operand' => '=', 'value' => 'fail'],
			['field' => 'backupstart', 'operand' => '>', 'value' => $last],
		];

		$failed = Platform::getInstance()->get_statistics_list(['filters' => $filters]);

		// Well, everything went ok.
		if (!$failed)
		{
			return [
				'message' => ["No need to run: no failed backups or notifications were already sent."],
				'result'  => true,
			];
		}

		// Whops! Something went wrong, let's start notifing
		$superAdmins     = [];
		$superAdminEmail = $this->container->params->get('failure_email_address', '');

		if (!empty($superAdminEmail))
		{
			$superAdmins = $this->getSuperUsers($superAdminEmail);
		}

		if (empty($superAdmins))
		{
			$superAdmins = $this->getSuperUsers();
		}

		if (empty($superAdmins))
		{
			return [
				'message' => ["WARNING! Failed backup(s) detected, but there are no configured Super Administrators to receive notifications"],
				'result'  => false,
			];
		}

		$failedReport = [];

		foreach ($failed as $fail)
		{
			$string = "Description : " . $fail['description'] . "\n";
			$string .= "Start time  : " . $fail['backupstart'] . "\n";
			$string .= "Origin      : " . $fail['origin'] . "\n";
			$string .= "Type        : " . $fail['type'] . "\n";
			$string .= "Profile ID  : " . $fail['profile_id'] . "\n";
			$string .= "Backup ID   : " . $fail['id'];

			$failedReport[] = $string;
		}

		$failedReport = implode("\n#-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+#\n", $failedReport);

		$email_subject = $this->container->params->get('failure_email_subject', '');

		if (!$email_subject)
		{
			$email_subject = <<<ENDSUBJECT
THIS EMAIL IS SENT FROM YOUR SITE "[SITENAME]" - Failed backup(s) detected
ENDSUBJECT;
		}

		$email_body = $this->container->params->get('failure_email_body', '');

		if (!$email_body)
		{
			$email_body = <<<ENDBODY
================================================================================
FAILED BACKUP ALERT
================================================================================

Your site has determined that there are failed backups.

The following backups are found to be failing:

[FAILEDLIST]

================================================================================
WHY AM I RECEIVING THIS EMAIL?
================================================================================

This email has been automatically sent by scritp you, or the person who built
or manages your site, has installed and explicitly configured. This script looks
for failed backups and sends an email notification to all Super Users.

If you do not understand what this means, please do not contact the authors of
the software. They are NOT sending you this email and they cannot help you.
Instead, please contact the person who built or manages your site.

================================================================================
WHO SENT ME THIS EMAIL?
================================================================================

This email is sent to you by your own site, [SITENAME]

ENDBODY;
		}

		$jconfig = $this->container->platform->getConfig();

		$mailfrom = $jconfig->get('mailfrom');
		$fromname = $jconfig->get('fromname');

		$email_subject = Factory::getFilesystemTools()->replace_archive_name_variables($email_subject);
		$email_body    = Factory::getFilesystemTools()->replace_archive_name_variables($email_body);
		$email_body    = str_replace('[FAILEDLIST]', $failedReport, $email_body);

		foreach ($superAdmins as $sa)
		{
			try
			{
				$mailer = JFactory::getMailer();

				$mailer->setSender([$mailfrom, $fromname]);
				$mailer->addRecipient($sa->email);
				$mailer->setSubject($email_subject);
				$mailer->setBody($email_body);
				$mailer->Send();
			}
			catch (Exception $e)
			{
				// Joomla! 3.5 is written by incompetent bonobos
			}
		}

		// Let's update the last time we check, so we will avoid to send
		// the same notification several times
		$this->updateLastCheck(intval($last));

		return [
			'message' => [
				"WARNING! Found " . count($failed) . " failed backup(s)",
				"Sent " . count($superAdmins) . " notifications",
			],
			'result'  => true,
		];
	}

	/**
	 * Delete the backup statistics record whose ID is set in the model
	 *
	 * @return  bool  True on success
	 */
	public function delete()
	{
		$db = $this->container->db;

		$id = $this->getState('id', 0);

		if ((!is_numeric($id)) || ($id <= 0))
		{
			throw new RecordNotLoaded(Text::_('COM_AKEEBA_BUADMIN_ERROR_INVALIDID'));
		}

		// Try to delete files. This will check (and stop) if any record is a frozen one
		$this->deleteFile();

		if (!Platform::getInstance()->delete_statistics($id))
		{
			throw new RuntimeException($db->getError(), 500);
		}

		return true;
	}

	/**
	 * Delete the backup file of the stats record whose ID is set in the model
	 *
	 * @return  bool  True on success
	 */
	public function deleteFile()
	{
		$id = $this->getState('id', 0);

		if ((!is_numeric($id)) || ($id <= 0))
		{
			throw new RecordNotLoaded(Text::_('COM_AKEEBA_BUADMIN_ERROR_INVALIDID'));
		}

		// Get the backup statistics record and the files to delete
		$stat     = (array) Platform::getInstance()->get_statistics($id);

		if ($stat['frozen'])
		{
			throw new FrozenRecordError(Text::_('COM_AKEEBA_BUADMIN_FROZENRECORD_ERROR'));
		}

		$allFiles = Factory::getStatistics()->get_all_filenames($stat, false);

		// Remove the custom log file if necessary
		$this->deleteLogs($stat);

		// No files? Nothing to do.
		if (empty($allFiles))
		{
			return true;
		}

		$status = true;

		foreach ($allFiles as $filename)
		{
			if (!@file_exists($filename))
			{
				continue;
			}

			$new_status = @unlink($filename);

			if (!$new_status)
			{
				$new_status = File::delete($filename);
			}

			$status = $status ? $new_status : false;
		}

		return $status;
	}

	/**
	 * Get a Joomla! pagination object
	 *
	 * @param   array  $filters  Filters to apply. See Platform::get_statistics_list
	 *
	 * @return  Pagination
	 */
	public function &getPagination($filters = null)
	{
		if (empty($this->pagination))
		{
			// Prepare pagination values
			$total      = Platform::getInstance()->get_statistics_count($filters);
			$limitstart = $this->getState('limitstart', 0);
			$limit      = $this->getState('limit', 10);

			// Create the pagination object
			$this->pagination = new Pagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}

	/**
	 * Set the flag to hide the restoration instructions modal from the Manage Backups page
	 *
	 * @return  void
	 */
	public function hideRestorationInstructionsModal()
	{
		$this->container->params->set('show_howtorestoremodal', 0);
		$this->container->params->save();
	}

	/**
	 * Freeze or melt a backup report
	 *
	 * @param array $ids        Array of backup IDs that should be updated
	 * @param int   $freeze     1= freeze, 0= melt
	 *
	 * @throws Exception
	 */
	public function freezeUnfreezeRecords(array $ids, $freeze)
	{
		if (!$ids)
		{
			return;
		}

		$freeze = (int) $freeze;

		foreach ($ids as $id)
		{
			// If anything wrong happens, let the exception bubble up, so it will be reported
			Platform::getInstance()->set_or_update_statistics($id, ['frozen' => $freeze]);
		}
	}

	/**
	 * Deletes the backup-specific log files of a stats record
	 *
	 * @param   array  $stat  The array holding the backup stats record
	 *
	 * @return  void
	 */
	protected function deleteLogs(array $stat)
	{
		// We can't delete logs if there is no backup ID in the record
		if (!isset($stat['backupid']) || empty($stat['backupid']))
		{
			return;
		}

		$logFileNames = [
			'akeeba.' . $stat['tag'] . '.' . $stat['backupid'] . '.log',
			'akeeba.' . $stat['tag'] . '.' . $stat['backupid'] . '.log.php',
		];

		foreach ($logFileNames as $logFileName)
		{
			$logPath = dirname($stat['absolute_path']) . '/' . $logFileName;

			if (@file_exists($logPath))
			{
				if (!@unlink($logPath))
				{
					File::delete($logPath);
				}
			}
		}
	}

	/**
	 * Returns the Super Users' email information. If you provide a comma separated $email list we will check that these
	 * emails do belong to Super Users and that they have not blocked reception of system emails.
	 *
	 * @param   null|string  $email  A list of Super Users to email, null for all Super Users
	 *
	 * @return  User[]  The list of Super User objects
	 */
	private function getSuperUsers($email = null)
	{
		// Convert the email list to an array
		$emails = [];

		if (!empty($email))
		{
			$temp   = explode(',', $email);
			$emails = [];

			foreach ($temp as $entry)
			{
				$emails[] = trim($entry);
			}

			$emails = array_unique($emails);
			$emails = array_map('strtolower', $emails);
		}

		// Get all usergroups with Super User access
		$db     = $this->getContainer()->db;
		$q      = $db->getQuery(true)
			->select([$db->qn('id')])
			->from($db->qn('#__usergroups'));
		$groups = $db->setQuery($q)->loadColumn();

		// Get the groups that are Super Users
		$groups = array_filter($groups, function ($gid) {
			return Access::checkGroup($gid, 'core.admin');
		});

		$userList = [];

		foreach ($groups as $gid)
		{
			$uids = Access::getUsersByGroup($gid);

			array_walk($uids, function ($uid, $index) use (&$userList) {
				$userList[$uid] = $this->container->platform->getUser($uid);
			});
		}

		if (empty($emails))
		{
			return $userList;
		}

		array_filter($userList, function (User $user) use ($emails) {
			return in_array(strtolower($user->email), $emails);
		});

		return $userList;
	}

	/**
	 * Update the time we last checked for failed backups
	 *
	 * @param   int  $exists  Any non zero value means that we update, not insert, the record
	 *
	 * @return  void
	 */
	private function updateLastCheck($exists)
	{
		$db = $this->container->db;

		$now      = new Date();
		$nowToSql = $now->toSql();

		$query = $db->getQuery(true)
			->insert($db->qn('#__ak_storage'))
			->columns([$db->qn('tag'), $db->qn('lastupdate')])
			->values($db->q('akeeba_checkfailed') . ', ' . $db->q($nowToSql));

		if ($exists)
		{
			$query = $db->getQuery(true)
				->update($db->qn('#__ak_storage'))
				->set($db->qn('lastupdate') . ' = ' . $db->q($nowToSql))
				->where($db->qn('tag') . ' = ' . $db->q('akeeba_checkfailed'));
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $exc)
		{
		}
	}

	/**
	 * Get the last update check date and time stamp
	 *
	 * @return  string
	 */
	private function getLastCheck()
	{
		$db = $this->container->db;

		$query = $db->getQuery(true)
			->select($db->qn('lastupdate'))
			->from($db->qn('#__ak_storage'))
			->where($db->qn('tag') . ' = ' . $db->q('akeeba_checkfailed'));

		$datetime = $db->setQuery($query)->loadResult();

		if (!intval($datetime))
		{
			$datetime = $db->getNullDate();
		}

		return $datetime;
	}
}
