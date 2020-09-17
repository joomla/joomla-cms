<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

// Old PHP version detected. EJECT! EJECT! EJECT!
if (!version_compare(PHP_VERSION, '7.1.0', '>='))
{
	return;
}

// Make sure Akeeba Backup is installed
if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba'))
{
	return;
}

// Joomla! version check
if (version_compare(JVERSION, '2.5', 'lt'))
{
	// Joomla! earlier than 2.5. Nope.
	return;
}


use Akeeba\Backup\Admin\Model\Statistics;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Date\Date;
use FOF30\Utils\CacheCleaner;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

// Deactivate self
$db    = JFactory::getDbo();
$query = $db->getQuery(true)
	->update($db->qn('#__extensions'))
	->set($db->qn('enabled') . ' = ' . $db->q('0'))
	->where($db->qn('element') . ' = ' . $db->q('akeebabackup'))
	->where($db->qn('folder') . ' = ' . $db->q('quickicon'));
$db->setQuery($query);
$db->execute();

// Load FOF if not already loaded
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	return;
}

CacheCleaner::clearPluginsCache();

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}
	$serverTimezone = @date_default_timezone_get();
	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}
	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}
	@date_default_timezone_set($serverTimezone);
}
/*
 * Hopefully, if we are still here, the site is running on at least PHP5. This means that
 * including the Akeeba Backup factory class will not throw a White Screen of Death, locking
 * the administrator out of the back-end.
 */

// Make sure Akeeba Backup is installed, or quit
$akeeba_installed = @file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupEngine/Factory.php');

if (!$akeeba_installed)
{
	return;
}

// Make sure Akeeba Backup is enabled
if (!ComponentHelper::isEnabled('com_akeeba'))
{
	return;
}

// Joomla! 1.6 or later - check ACLs (and not display when the site is bricked,
// hopefully resulting in no stupid emails from users who think that somehow
// Akeeba Backup crashed their site). It also not displays the button to people
// who are not authorised to take backups - which makes perfect sense!
$continueLoadingIcon = true;
$user                = JFactory::getUser();

if (!$user->authorise('akeeba.backup', 'com_akeeba'))
{
	$continueLoadingIcon = false;
}

// Do we really, REALLY have Akeeba Engine?
if ($continueLoadingIcon)
{
	if (!defined('AKEEBAENGINE'))
	{
		define('AKEEBAENGINE', 1); // Required for accessing Akeeba Engine's factory class
	}
	try
	{
		@include_once JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupEngine/Factory.php';
		if (!class_exists('\Akeeba\Engine\Factory', false))
		{
			$continueLoadingIcon = false;
		}
	}
	catch (Exception $e)
	{
		$continueLoadingIcon = false;
	}
}

// Enable self if we have to bail out
if (!$continueLoadingIcon)
{
	$db    = JFactory::getDbo();
	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('enabled') . ' = ' . $db->q('1'))
		->where($db->qn('element') . ' = ' . $db->q('akeebabackup'))
		->where($db->qn('folder') . ' = ' . $db->q('quickicon'));
	$db->setQuery($query);
	$db->execute();

	CacheCleaner::clearPluginsCache();

	return;
}
unset($continueLoadingIcon);

/**
 * Akeeba Backup Notification plugin
 */
class plgQuickiconAkeebabackup extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 *
	 * @since       2.5
	 */
	public function __construct(&$subject, $config)
	{
		/**
		 * I know that this piece of code cannot possibly be executed since I have already returned BEFORE declaring
		 * the class when eAccelerator is detected. However, eAccelerator is being dumb. It will return above BUT it
		 * will also declare the class EVEN THOUGH according to how PHP works this part of the code should be
		 * unreachable o_O Therefore I have to define this constant and exit the constructor when we have already
		 * determined that this class MUST NOT be defined.
		 */
		if (defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			return;
		}

		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array A list of icon definition associative arrays, consisting of the
	 *                 keys link, image, text and access.
	 *
	 * @throws  Exception
	 */
	public function onGetIcons($context)
	{
		$container           = Container::getInstance('com_akeeba');
		$user                = $container->platform->getUser();
		$j4WarningJavascript = false;

		if (!$user->authorise('akeeba.backup', 'com_akeeba'))
		{
			return;
		}

		/**
		 * The context in which quickicons appear. There's a reason this is hardcoded now.
		 *
		 * Joomla 3. This is always mod_quickicon. Grouping is defined by the 'group' key of the returned array. This is
		 * the sane way I personally wrote this feature when I contributed it to Joomla! 1.7. The whole point of the
		 * 'context' was that you could have **extension specific** quick icon plugins. Think about how JCE shows icons
		 * in its control panel. The incoming context determines which plugins to load, the returned group key
		 * determines how the icons are grouped in the context.
		 *
		 * Joomla 4. The context defines the quick icon grouping. The 'group' key of the returned array is ignored. All
		 * quick icon plugins which respond to the 'mod_quickicon' context are shown in the "Third party" backend
		 * module. This is a nonsensical change.
		 *
		 * Unfortunately, this means that I have to remove the user-defined context option. The reason is that Joomla
		 * renders plugin options based on a static XML file which is common for J3 and J4. However, the context has a
		 * different meaning and requires a different setting for J3 and J4. I have to take the flexibility away from
		 * the user and force a default context in J4 which puts our icon in Update Checks.
		 *
		 * Yes, I know that the Update Checks module is, at the very least, mislabeled. There are of course the updates
		 * to Joomla and extensions but also privacy requests and overrides, the latter two not being updates in any
		 * conceivable form and in any possible universe. Since this backend module is supposed to have everything I am
		 * going to throw my backup check in there. At least my plugin shows "backup up-to-date" or "update needed"
		 * which actually makes it FAR MORE RELEVANT in an "updates" area on the page than the friggin' privacy
		 * requests!
		 */
		$configuredContext = version_compare(JVERSION, '3.999.999', 'gt') ? 'update_quickicon' : 'mod_quickicon';

		/**/
		if (
			$context != $configuredContext
			|| !JFactory::getUser()->authorise('core.manage', 'com_installer')
		)
		{
			return;
		}
		/**/

		// Necessary defines for Akeeba Engine
		if (!defined('AKEEBAENGINE'))
		{
			define('AKEEBAENGINE', 1);
			define('AKEEBAROOT', $container->backEndPath . '/BackupEngine');
			define('ALICEROOT', $container->backEndPath . '/AliceEngine');

			// Make sure we have a profile set throughout the component's lifetime
			$profile_id = $container->platform->getSessionVar('profile', null, 'akeeba');

			if (is_null($profile_id))
			{
				$container->platform->setSessionVar('profile', 1, 'akeeba');
			}

			// Load Akeeba Engine
			require_once $container->backEndPath . '/BackupEngine/Factory.php';
		}

		Platform::addPlatform('joomla3x', JPATH_ADMINISTRATOR . '/components/com_akeeba/BackupPlatform/Joomla3x');

		$url = Uri::base();
		$url = rtrim($url, '/');

		$profileId = (int) $this->params->get('profileid', 1);
		$token     = $container->platform->getToken(true);

		if ($profileId <= 0)
		{
			$profileId = 1;
		}

		$isJoomla4 = version_compare(JVERSION, '3.999999.999999', 'gt');

		$ret = [
			'link'  => 'index.php?option=com_akeeba&view=Backup&autostart=1&returnurl=' . base64_encode($url) . '&profileid=' . $profileId . "&$token=1",
			'image' => 'akeeba-black',
			'text'  => Text::_('PLG_QUICKICON_AKEEBABACKUP_OK'),
			'id'    => 'plg_quickicon_akeebabackup',
			'group' => 'MOD_QUICKICON_MAINTENANCE',
		];

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$ret['image'] = $url . '/../media/com_akeeba/icons/akeeba-48.png';
		}

		if ($isJoomla4)
		{
			$ret['image'] = 'fa fa-akeeba-black';
		}

		if ($this->params->get('enablewarning', 0) == 0)
		{
			// Process warnings
			$warning = false;

			$aeconfig = Factory::getConfiguration();
			Platform::getInstance()->load_configuration(1);

			// Get latest non-SRP backup ID
			$filters  = [
				[
					'field'   => 'tag',
					'operand' => '<>',
					'value'   => 'restorepoint',
				],
			];
			$ordering = [
				'by'    => 'backupstart',
				'order' => 'DESC',
			];

			/** @var Statistics $model */
			$model = $container->factory->model('Statistics')->tmpInstance();
			$list  = $model->getStatisticsListWithMeta(false, $filters, $ordering);

			if (!empty($list))
			{
				$record = (object) array_shift($list);
			}
			else
			{
				$record = null;
			}

			// Process "failed backup" warnings, if specified
			if ($this->params->get('warnfailed', 0) == 0)
			{
				if (!is_null($record))
				{
					$warning = (($record->status == 'fail') || ($record->status == 'run'));
				}
			}

			// Process "stale backup" warnings, if specified
			if (is_null($record))
			{
				$warning = true;
			}
			else
			{
				$maxperiod        = $this->params->get('maxbackupperiod', 24);
				$lastBackupRaw    = $record->backupstart;
				$lastBackupObject = new Date($lastBackupRaw);
				$lastBackup       = $lastBackupObject->toUnix();
				$maxBackup        = time() - $maxperiod * 3600;
				if (!$warning)
				{
					$warning = ($lastBackup < $maxBackup);
				}
			}

			if ($warning)
			{
				$ret['image'] = 'akeeba-red';
				$ret['text']  = Text::_('PLG_QUICKICON_AKEEBABACKUP_BACKUPREQUIRED');

				if ($isJoomla4)
				{
					/**
					 * Joomla! 4 is dumb. Quickicons cannot have a class. However, Joomla! itself uses a class on the icon
					 * container to tell users when the update status is OK or there are updates required. Therefore we will
					 * have to use some Javascript to achieve the same result. Grrrr...
					 */
					$j4WarningJavascript = true;
					$ret['image']        = 'fa fa-akeeba-red';
				}
				elseif (version_compare(JVERSION, '3.0', 'lt'))
				{
					$ret['image'] = $url . '/../media/com_akeeba/icons/akeeba-warning-48.png';
				}
				else
				{
					$ret['text'] = '<span class="badge badge-important">' . $ret['text'] . '</span>';
				}
			}
		}

		if (version_compare(JVERSION, '3.0', 'gt'))
		{
			$inlineCSS = <<< CSS
@font-face
{
	font-family: "Akeeba Products for Quickicons";
	font-style: normal;
	font-weight: normal;
	src: url("../media/com_akeeba/fonts/akeeba/Akeeba-Products.woff") format("woff"); 
}

[class*=fa-akeeba-]:before
{
  display: inline-block;
  font-family: 'Akeeba Products for Quickicons';
  font-style: normal;
  font-weight: normal;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  position: relative;
  -moz-osx-font-smoothing: grayscale;
}

span.fa-akeeba-black:before,
div.fa-akeeba-black:before
{
  color: var(--success);
  background: transparent;
}

span.fa-akeeba-red:before,
div.fa-akeeba-red:before
{
  color: var(--danger);
  background: transparent;
}

span[class*=fa-akeeba]:before,
div[class*=fa-akeeba]:before
{
	content: 'B';
}

.icon-akeeba-black {
	background-image: url("../media/com_akeeba/icons/akeebabackup-16-black.png");
	width: 16px;
	height: 16px;
}

.icon-akeeba-red {
	background-image: url("../media/com_akeeba/icons/akeebabackup-16-red.png");
	width: 16px;
	height: 16px;
}

.quick-icons .nav-list [class^="icon-akeeba-"], .quick-icons .nav-list [class*=" icon-akeeba-"] {
	margin-right: 7px;
}

.quick-icons .nav-list [class^="icon-akeeba-red"], .quick-icons .nav-list [class*=" icon-akeeba-red"] {
	margin-bottom: -4px;
}
CSS;

			JFactory::getApplication()->getDocument()->addStyleDeclaration($inlineCSS);
		}

		if ($isJoomla4)
		{
			$myClass  = $j4WarningJavascript ? 'danger' : 'success';
			$inlineJS = <<< JS
// ; Defense against third party broken Javascript
document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('plg_quickicon_akeebabackup').className = 'pulse $myClass';
});

JS;

			JFactory::getApplication()->getDocument()->addScriptDeclaration($inlineJS);
		}

		// Re-enable self
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__extensions'))
			->set($db->qn('enabled') . ' = ' . $db->q('1'))
			->where($db->qn('element') . ' = ' . $db->q('akeebabackup'))
			->where($db->qn('folder') . ' = ' . $db->q('quickicon'));
		$db->setQuery($query);
		$db->execute();

		CacheCleaner::clearPluginsCache();

		return [$ret];
	}
}
