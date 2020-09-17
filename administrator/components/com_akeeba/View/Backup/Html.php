<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Backup;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Helper\Status;
use Akeeba\Backup\Admin\Helper\Utils;
use Akeeba\Backup\Admin\Model\Backup;
use Akeeba\Backup\Admin\Model\ControlPanel;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileList;
use Akeeba\Engine\Factory;
use FOF30\Date\Date;
use FOF30\View\DataView\Html as BaseView;
use Joomla\CMS\Language\Text;

/**
 * View controller for the Backup Now page
 */
class Html extends BaseView
{
	use ProfileList, ProfileIdAndName;

	/**
	 * Do we have errors preventing the backup from starting?
	 *
	 * @var  bool
	 */
	public $hasErrors = false;

	/**
	 * Do we have warnings which may affect –but do not prevent– the backup from running?
	 *
	 * @var  bool
	 */
	public $hasWarnings = false;

	/**
	 * The HTML of the warnings cell
	 *
	 * @var  string
	 */
	public $warningsCell = '';

	/**
	 * Backup description
	 *
	 * @var  string
	 */
	public $description = '';

	/**
	 * Default backup description
	 *
	 * @var  string
	 */
	public $defaultDescription = '';

	/**
	 * Backup comment
	 *
	 * @var  string
	 */
	public $comment = '';

	/**
	 * JSON string of the backup domain name to titles associative array
	 *
	 * @var  array
	 */
	public $domains = '';

	/**
	 * Maximum execution time in seconds
	 *
	 * @var  int
	 */
	public $maxExecutionTime = 10;

	/**
	 * Execution time bias, in percentage points (0-100)
	 *
	 * @var  int
	 */
	public $runtimeBias = 75;

	/**
	 * URL to return to after the backup is complete
	 *
	 * @var  string
	 */
	public $returnURL = '';

	/**
	 * Is the output directory unwritable?
	 *
	 * @var  bool
	 */
	public $unwriteableOutput = false;

	/**
	 * Should I show the JPS password field? 0/1.
	 *
	 * @var  int
	 */
	public $showJPSPassword = 0;

	/**
	 * JPS password
	 *
	 * @var  string
	 */
	public $jpsPassword = '';

	/**
	 * Should I show the ANGIE password field? 0/1.
	 *
	 * @var  int
	 */
	public $showANGIEPassword = 0;

	/**
	 * ANGIE password
	 *
	 * @var  string
	 */
	public $ANGIEPassword = '';

	/**
	 * Should I autostart the backup?
	 *
	 * @var  string
	 */
	public $autoStart = false;

	/**
	 * Should I display desktop notifications? 0/1
	 *
	 * @var  int
	 */
	public $desktopNotifications = 0;

	/**
	 * Should I try to automatically resume the backup in case of an error? 0/1
	 *
	 * @var  int
	 */
	public $autoResume = 0;

	/**
	 * After how many seconds should I try to automatically resume the backup?
	 *
	 * @var  int
	 */
	public $autoResumeTimeout = 10;

	/**
	 * How many times in total should I try to automatically resume the backup?
	 *
	 * @var  int
	 */
	public $autoResumeRetries = 3;

	/**
	 * Should I prompt the user to run the Configuration Wizard?
	 *
	 * @var  bool
	 */
	public $promptForConfigurationWizard = false;

	/**
	 * Runs before displaying the backup page
	 */
	public function onBeforeMain()
	{
		// Load the view-specific Javascript
		$this->container->template->addJS('media://com_akeeba/js/Backup.min.js', true, false, $this->container->mediaVersion);

		// Load the models
		/** @var  Backup $model */
		$model = $this->getModel();

		/** @var ControlPanel $cpanelmodel */
		$cpanelmodel = $this->container->factory->model('ControlPanel')->tmpInstance();

		// Load the Status Helper
		$helper = Status::getInstance();

		// Determine default description
		$default_description = $this->getDefaultDescription();

		// Load data from the model state
		$backup_description = $model->getState('description', $default_description, 'string');
		$comment            = $model->getState('comment', '', 'html');
		$returnurl          = Utils::safeDecodeReturnUrl($model->getState('returnurl', ''));

		// Get the maximum execution time and bias
		$engineConfiguration = Factory::getConfiguration();
		$maxexec             = $engineConfiguration->get('akeeba.tuning.max_exec_time', 14) * 1000;
		$bias                = $engineConfiguration->get('akeeba.tuning.run_time_bias', 75);

		// Check if the output directory is writable
		$warnings         = Factory::getConfigurationChecks()->getDetailedStatus();
		$unwritableOutput = array_key_exists('001', $warnings);

		// Pass on data
		$this->getProfileList();
		$this->getProfileIdAndName();

		$this->hasErrors                    = !$helper->status;
		$this->hasWarnings                  = $helper->hasQuirks();
		$this->warningsCell                 = $helper->getQuirksCell(!$helper->status);
		$this->description                  = $backup_description;
		$this->defaultDescription           = $default_description;
		$this->comment                      = $comment;
		$this->domains                      = $this->getDomains();
		$this->maxExecutionTime             = $maxexec;
		$this->runtimeBias                  = $bias;
		$this->returnURL                    = $returnurl;
		$this->unwriteableOutput            = $unwritableOutput;
		$this->autoStart                    = $model->getState('autostart', 0, 'boolean');
		$this->desktopNotifications         = $this->container->params->get('desktop_notifications', '0') ? 1 : 0;
		$this->autoResume                   = $engineConfiguration->get('akeeba.advanced.autoresume', 1);
		$this->autoResumeTimeout            = $engineConfiguration->get('akeeba.advanced.autoresume_timeout', 10);
		$this->autoResumeRetries            = $engineConfiguration->get('akeeba.advanced.autoresume_maxretries', 3);
		$this->promptForConfigurationWizard = $engineConfiguration->get('akeeba.flag.confwiz', 0) == 0;

		if ($engineConfiguration->get('akeeba.advanced.archiver_engine', 'jpa') == 'jps')
		{
			$this->showJPSPassword = 1;
			$this->jpsPassword     = $engineConfiguration->get('engine.archiver.jps.key', '');
		}

		// Always show ANGIE password: we add that feature to the Core version as well
		$this->showANGIEPassword = 1;
		$this->ANGIEPassword     = $engineConfiguration->get('engine.installer.angie.key', '');
	}

	/**
	 * Get the default description for this backup attempt
	 *
	 * @return  string
	 */
	private function getDefaultDescription()
	{
		$tzDefault           = $this->container->platform->getConfig()->get('offset');
		$user                = $this->container->platform->getUser();
		$tz                  = $user->getParam('timezone', $tzDefault);
		$dateNow             = new Date('now', $tz);
		$default_description = Text::_('COM_AKEEBA_BACKUP_DEFAULT_DESCRIPTION') . ' ' .
			$dateNow->format(Text::_('DATE_FORMAT_LC2'), true);

		return $default_description;
	}

	/**
	 * Get a list of backup domain keys and titles
	 *
	 * @return  array
	 */
	private function getDomains()
	{
		$engineConfiguration = Factory::getConfiguration();
		$script              = $engineConfiguration->get('akeeba.basic.backup_type', 'full');
		$scripting           = Factory::getEngineParamsProvider()->loadScripting();
		$domains             = [];

		if (empty($scripting))
		{
			return $domains;
		}

		foreach ($scripting['scripts'][$script]['chain'] as $domain)
		{
			$description = Text::_($scripting['domains'][$domain]['text']);
			$domain_key  = $scripting['domains'][$domain]['domain'];
			$domains[]   = [$domain_key, $description];
		}

		return $domains;
	}
}
