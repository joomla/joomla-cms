<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Manage;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Backup\Admin\Model\Statistics;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use DateTimeZone;
use Exception;
use FOF30\Date\Date;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri as JUri;
use stdClass;

/**
 * View controller for the Backup Now page
 */
class Html extends BaseView
{
	/**
	 * Should I use the user's local time zone for display?
	 *
	 * @var  boolean
	 */
	public $useLocalTime;

	/**
	 * Time format string to use for the time zone suffix
	 *
	 * @var  string
	 */
	public $timeZoneFormat;

	/**
	 * The backup record for the showcomment view
	 *
	 * @var  array
	 */
	public $record = [];

	/**
	 * The backup record ID for the showcomment view
	 *
	 * @var  int
	 */
	public $record_id = 0;

	/**
	 * List of Profiles objects
	 *
	 * @var  array
	 */
	public $profiles = [];

	/**
	 * List of profiles for JHtmlSelect
	 *
	 * @var  array
	 */
	public $profilesList = [];

	/**
	 * List of frozen options for JHtmlSelect
	 *
	 * @var  array
	 */
	public $frozenList = [];

	/**
	 * Order direction, ASC/DESC
	 *
	 * @var  string
	 */
	public $order_Dir = 'DESC';

	/**
	 * Description filter
	 *
	 * @var string
	 */
	public $fltDescription = '';

	/**
	 * From date filter
	 *
	 * @var  string
	 */
	public $fltFrom = '';

	/**
	 * To date filter
	 *
	 * @var  string
	 */
	public $fltTo = '';

	/**
	 * Origin filter
	 *
	 * @var  string
	 */
	public $fltOrigin = '';

	/**
	 * Profile filter
	 *
	 * @var  string
	 */
	public $fltProfile = '';

	/**
	 * Frozen records filter
	 *
	 * @var string
	 */
	public $fltFrozen = '';

	/**
	 * List of records to display
	 *
	 * @var  array
	 */
	public $items = [];

	/**
	 * Pagination object
	 *
	 * @var Pagination
	 */
	public $pagination = null;

	/**
	 * Date format for the backup start time
	 *
	 * @var  string
	 */
	public $dateFormat = '';

	/**
	 * Should I pormpt the user ot run the configuration wizard?
	 *
	 * @var  bool
	 */
	public $promptForBackupRestoration = false;

	/**
	 * Sorting order options
	 *
	 * @var  array
	 */
	public $sortFields = [];

	/**
	 * Cache the user permissions
	 *
	 * @var   array
	 *
	 * @since 5.3.0
	 */
	public $permissions = [];

	/**
	 * List the backup records
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function onBeforeMain()
	{
		// Load custom Javascript for this page
		$this->container->template->addJS('media://com_akeeba/js/Manage.min.js', true, false, $this->container->mediaVersion);

		$user              = $this->container->platform->getUser();
		$this->permissions = [
			'configure' => $user->authorise('akeeba.configure', 'com_akeeba'),
			'backup'    => $user->authorise('akeeba.backup', 'com_akeeba'),
			'download'  => $user->authorise('akeeba.download', 'com_akeeba'),
		];


		/** @var Profiles $profilesModel */
		$profilesModel           = $this->container->factory->model('Profiles')->tmpInstance();
		$enginesPerPprofile      = $profilesModel->getPostProcessingEnginePerProfile();
		$this->enginesPerProfile = $enginesPerPprofile;

		// "Show warning first" download button.
		JText::script('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD_CONFIRM', false);
		$this->container->platform->addScriptOptions('akeeba.Manage.baseURI', JUri::base());

		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			JHtml::_('behavior.calendar');
		}

		$hash = 'akeebamanage';

		// ...ordering
		$platform = $this->container->platform;
		$input    = $this->input;

		// ...filter state
		$this->fltDescription   = $platform->getUserStateFromRequest($hash . 'filter_description', 'description', $input, '');
		$this->fltFrom          = $platform->getUserStateFromRequest($hash . 'filter_from', 'from', $input, '');
		$this->fltTo            = $platform->getUserStateFromRequest($hash . 'filter_to', 'to', $input, '');
		$this->fltOrigin        = $platform->getUserStateFromRequest($hash . 'filter_origin', 'origin', $input, '');
		$this->fltProfile       = $platform->getUserStateFromRequest($hash . 'filter_profile', 'profile', $input, '');
		$this->fltFrozen        = $platform->getUserStateFromRequest($hash . 'filter_frozen', 'frozen', $input, '');

		$this->lists            = new stdClass();
		$this->lists->order     = $platform->getUserStateFromRequest($hash . 'filter_order', 'filter_order', $input, 'backupstart');
		$this->lists->order_Dir = $platform->getUserStateFromRequest($hash . 'filter_order_Dir', 'filter_order_Dir', $input, 'DESC');

		$filters  = $this->getFilters();
		$ordering = $this->getOrdering();

		/** @var Statistics $model */
		$model       = $this->getModel();
		$this->items = $model->getStatisticsListWithMeta(false, $filters, $ordering);

		// Default limits
		$defaultLimit = 20;

		if (!$this->container->platform->isCli() && class_exists('JFactory'))
		{
			$app = JFactory::getApplication();

			if (method_exists($app, 'get'))
			{
				$defaultLimit = $app->get('list_limit');
			}
		}

		$this->lists->limitStart = $model->getState('limitstart', 0, 'int');
		$this->lists->limit      = $model->getState('limit', $defaultLimit, 'int');

		// Let's create an array indexed with the profile id for better handling
		$profiles = $profilesModel->get(true);

		$profilesList = [
			JHtml::_('select.option', '', '–' . JText::_('COM_AKEEBA_BUADMIN_LABEL_PROFILEID') . '–'),
		];

		if (!empty($profiles))
		{
			foreach ($profiles as $profile)
			{
				$profilesList[] = JHtml::_('select.option', $profile->id, '#' . $profile->id . '. ' . $profile->description);
			}
		}

		// Assign data to the view
		$this->profiles     = $profiles; // Profiles
		$this->profilesList = $profilesList; // Profiles list for select box
		$this->itemCount    = count($this->items);
		$this->pagination   = $model->getPagination($filters); // Pagination object

		$this->frozenList = [
			JHtml::_('select.option', '', '–' . JText::_('COM_AKEEBA_BUADMIN_LABEL_FROZEN_SELECT') . '–'),
			JHtml::_('select.option', '1', JText::_('COM_AKEEBA_BUADMIN_LABEL_FROZEN_FROZEN')),
			JHtml::_('select.option', '2', JText::_('COM_AKEEBA_BUADMIN_LABEL_FROZEN_UNFROZEN')),
		];

		if ($this->lists->order_Dir)
		{
			$this->lists->order_Dir = strtolower($this->lists->order_Dir);
		}

		// Date format
		$dateFormat       = $this->container->params->get('dateformat', '');
		$dateFormat       = trim($dateFormat);
		$this->dateFormat = !empty($dateFormat) ? $dateFormat : JText::_('DATE_FORMAT_LC4');

		// Time zone options
		$this->useLocalTime   = $this->container->params->get('localtime', '1') == 1;
		$this->timeZoneFormat = $this->container->params->get('timezonetext', 'T');

		// Should I show the prompt for the configuration wizard?
		$this->promptForBackupRestoration = $this->container->params->get('show_howtorestoremodal', 1) != 0;

		// Construct the array of sorting fields
		$this->sortFields = [
			'id'          => JText::_('COM_AKEEBA_BUADMIN_LABEL_ID'),
			'description' => JText::_('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION'),
			'backupstart' => JText::_('COM_AKEEBA_BUADMIN_LABEL_START'),
			'profile_id'  => JText::_('COM_AKEEBA_BUADMIN_LABEL_PROFILEID'),
		];
	}

	/**
	 * Edit a backup record's description and comment
	 *
	 * @return  void
	 */
	public function onBeforeShowcomment()
	{
		/** @var Statistics $model */
		$model           = $this->getModel();
		$id              = $model->getState('id', 0, 'int');
		$record          = Platform::getInstance()->get_statistics($id);
		$this->record    = $record;
		$this->record_id = $id;

		$this->setLayout('comment');
	}

	/**
	 * File size formatting function. COnverts number of bytes to a human readable represenation.
	 *
	 * @param   int     $sizeInBytes         Size in bytes
	 * @param   int     $decimals            How many decimals should I use? Default: 2
	 * @param   string  $decSeparator        Decimal separator
	 * @param   string  $thousandsSeparator  Thousands grouping character
	 *
	 * @return string
	 */
	public function formatFilesize($sizeInBytes, $decimals = 2, $decSeparator = '.', $thousandsSeparator = '')
	{
		if ($sizeInBytes <= 0)
		{
			return '-';
		}

		$units = ['b', 'KB', 'MB', 'GB', 'TB'];
		$unit  = floor(log($sizeInBytes, 2) / 10);

		if ($unit == 0)
		{
			$decimals = 0;
		}

		if (version_compare(PHP_VERSION, '5.6.0', 'lt'))
		{
			return number_format($sizeInBytes / 1024 ** $unit, $decimals, $decSeparator, $thousandsSeparator) . ' ' . $units[$unit];
		}

		return number_format($sizeInBytes / (1024 ** $unit), $decimals, $decSeparator, $thousandsSeparator) . ' ' . $units[$unit];
	}

	/**
	 * Translates the internal backup type (e.g. cli) to a human readable string
	 *
	 * @param   string  $recordType  The internal backup type
	 *
	 * @return  string
	 */
	public function translateBackupType($recordType)
	{
		static $backup_types = null;

		if (!is_array($backup_types))
		{
			// Load a mapping of backup types to textual representation
			$scripting    = Factory::getEngineParamsProvider()->loadScripting();
			$backup_types = [];
			foreach ($scripting['scripts'] as $key => $data)
			{
				$backup_types[$key] = JText::_($data['text']);
			}
		}

		if (array_key_exists($recordType, $backup_types))
		{
			return $backup_types[$recordType];
		}

		return '&ndash;';
	}

	/**
	 * Returns the origin's translated name and the appropriate icon class
	 *
	 * @param   array  $record  A backup record
	 *
	 * @return  array  array(originTranslation, iconClass)
	 */
	protected function getOriginInformation($record)
	{
		$originLanguageKey = 'COM_AKEEBA_BUADMIN_LABEL_ORIGIN_' . $record['origin'];
		$originDescription = JText::_($originLanguageKey);

		switch (strtolower($record['origin']))
		{
			case 'backend':
				$originIcon = 'akion-android-desktop';
				break;

			case 'frontend':
				$originIcon = 'akion-ios-world';
				break;

			case 'json':
				$originIcon = 'akion-android-cloud';
				break;

			case 'cli':
				$originIcon = 'akion-ios-paper-outline';
				break;

			case 'xmlrpc':
				$originIcon = 'akion-code';
				break;

			case 'lazy':
				$originIcon = 'akion-cube';
				break;

			default:
				$originIcon = 'akion-help';
				break;
		}

		if (empty($originLanguageKey) || ($originDescription == $originLanguageKey))
		{
			$originDescription = '&ndash;';
			$originIcon        = 'akion-help';

			return [$originDescription, $originIcon];
		}

		return [$originDescription, $originIcon];
	}

	/**
	 * Get the start time and duration of a backup record
	 *
	 * @param   array  $record  A backup record
	 *
	 * @return  array  array(startTimeAsString, durationAsString)
	 */
	protected function getTimeInformation($record)
	{
		$utcTimeZone = new DateTimeZone('UTC');
		$startTime   = new Date($record['backupstart'], $utcTimeZone);
		$endTime     = new Date($record['backupend'], $utcTimeZone);

		$duration = $endTime->toUnix() - $startTime->toUnix();

		if ($duration > 0)
		{
			$seconds  = $duration % 60;
			$duration = $duration - $seconds;

			$minutes  = ($duration % 3600) / 60;
			$duration = $duration - $minutes * 60;

			$hours    = $duration / 3600;
			$duration = sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
		}
		else
		{
			$duration = '';
		}

		$user   = $this->container->platform->getUser();
		$userTZ = $user->getParam('timezone', 'UTC');
		$tz     = new DateTimeZone($userTZ);
		$startTime->setTimezone($tz);

		$timeZoneSuffix = '';

		if (!empty($this->timeZoneFormat))
		{
			$timeZoneSuffix = $startTime->format($this->timeZoneFormat, $this->useLocalTime);
		}

		return [
			$startTime->format($this->dateFormat, $this->useLocalTime),
			$duration,
			$timeZoneSuffix,
		];
	}

	/**
	 * Get the class and icon for the backup status indicator
	 *
	 * @param   array  $record  A backup record
	 *
	 * @return  array  array(class, icon)
	 */
	protected function getStatusInformation($record)
	{
		$statusClass = '';

		switch ($record['meta'])
		{
			case 'ok':
				$statusIcon  = 'akion-checkmark';
				$statusClass = 'akeeba-label--green';
				break;
			case 'pending':
				$statusIcon  = 'akion-play';
				$statusClass = 'akeeba-label--orange';
				break;
			case 'fail':
				$statusIcon  = 'akion-android-cancel';
				$statusClass = 'akeeba-label--red';
				break;
			case 'remote':
				$statusIcon  = 'akion-cloud';
				$statusClass = 'akeeba-label--teal';
				break;
			default:
				$statusIcon  = 'akion-trash-a';
				$statusClass = 'akeeba-label--grey';
				break;
		}

		return [$statusClass, $statusIcon];
	}

	/**
	 * Get the profile name for the backup record (or "–" if the profile no longer exists)
	 *
	 * @param   array  $record  A backup record
	 *
	 * @return  string
	 */
	protected function getProfileName($record)
	{
		$profileName = '&mdash;';

		if (isset($this->profiles[$record['profile_id']]))
		{
			$profileName = $this->escape($this->profiles[$record['profile_id']]->description);

			return $profileName;
		}

		return $profileName;
	}

	/**
	 * Get the filters in a format that Akeeba Engine understands
	 *
	 * @return  array
	 */
	private function getFilters()
	{
		$filters = [];

		if ($this->fltDescription)
		{
			$filters[] = [
				'field'   => 'description',
				'operand' => 'LIKE',
				'value'   => $this->fltDescription,
			];
		}

		if ($this->fltFrom && $this->fltTo)
		{
			$filters[] = [
				'field'   => 'backupstart',
				'operand' => 'BETWEEN',
				'value'   => $this->fltFrom,
				'value2'  => $this->fltTo,
			];
		}
		elseif ($this->fltFrom)
		{
			$filters[] = [
				'field'   => 'backupstart',
				'operand' => '>=',
				'value'   => $this->fltFrom,
			];
		}
		elseif ($this->fltTo)
		{
			$toDate = new Date($this->fltTo);
			$to     = $toDate->format('Y-m-d') . ' 23:59:59';

			$filters[] = [
				'field'   => 'backupstart',
				'operand' => '<=',
				'value'   => $to,
			];
		}

		if ($this->fltOrigin)
		{
			$filters[] = [
				'field'   => 'origin',
				'operand' => '=',
				'value'   => $this->fltOrigin,
			];
		}

		if ($this->fltProfile)
		{
			$filters[] = [
				'field'   => 'profile_id',
				'operand' => '=',
				'value'   => (int) $this->fltProfile,
			];
		}

		if ($this->fltFrozen == 1)
		{
			$filters[] = [
				'field'   => 'frozen',
				'operand' => '=',
				'value'   => 1,
			];
		}
		elseif ($this->fltFrozen == 2)
		{
			$filters[] = [
				'field'   => 'frozen',
				'operand' => '=',
				'value'   => 0,
			];
		}

		if (empty($filters))
		{
			$filters = null;
		}

		return $filters;
	}

	/**
	 * Get the list ordering in a format that Akeeba Engine understands
	 *
	 * @return  array
	 */
	private function getOrdering()
	{
		$order = [
			'by'    => $this->lists->order,
			'order' => strtoupper($this->lists->order_Dir),
		];

		return $order;
	}

}
