<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Updater\Updater;
use Joomla\Database\ParameterType;

/**
 * Joomla! Update's Default View
 *
 * @since  2.5.4
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array with the Joomla! update information.
	 *
	 * @var    array
	 *
	 * @since  3.6.0
	 */
	protected $updateInfo = null;

	/**
	 * PHP options.
	 *
	 * @var   array  Array of PHP config options
	 *
	 * @since 3.10.0
	 */
	protected $phpOptions = null;

	/**
	 * PHP settings.
	 *
	 * @var   array  Array of PHP settings
	 *
	 * @since 3.10.0
	 */
	protected $phpSettings = null;

	/**
	 * Non Core Extensions.
	 *
	 * @var   array  Array of Non-Core-Extensions
	 *
	 * @since 3.10.0
	 */
	protected $nonCoreExtensions = null;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  4.0.0
	 */
	protected $state;

	/**
	 * Flag if the update component itself has to be updated
	 *
	 * @var boolean  True when update is available otherwise false
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $selfUpdateAvailable = false;

	/**
	 * Flag if we're in the upload form
	 *
	 * @var boolean  True when upload form should be visible otherwise false
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $showUploadAndUpdate = false;

	/**
	 * Warnings for the upload update
	 *
	 * @var array  An array of warnings which could prevent the upload update
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $warnings = [];

	/**
	 * A special prefix used for the emptystate layout variable
	 *
	 * @var string  The prefix
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $messagePrefix = '';

	/**
	 * Renders the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @since  2.5.4
	 */
	public function display($tpl = null)
	{
		$this->updateInfo          = $this->get('UpdateInformation');
		$this->selfUpdateAvailable = $this->checkForSelfUpdate();

		$this->state = $this->get('State');

		$hasUpdate = !empty($this->updateInfo['hasUpdate']);
		$hasDownload = isset($this->updateInfo['object']->downloadurl->_data);

		// Only Super Users have access to the Update & Install for obvious security reasons
		$this->showUploadAndUpdate = Factory::getUser()->authorise('core.admin') && $this->getLayout() === 'upload';

		$this->addToolbar();

		// There is an update for the updater itself. So we have to update it first
		if ($this->selfUpdateAvailable)
		{
			$this->setLayout('selfupdate');
		}
		// User requests the manual update and is an admin
		elseif ($this->showUploadAndUpdate)
		{
			$language = Factory::getLanguage();
			$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
			$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

			$this->warnings = $this->get('Items', 'warnings');
		}
		elseif (!$hasDownload || !$hasUpdate)
		{
			// Could be that we have a download file but no update, so we offer an re-install
			if ($hasDownload)
			{
				// We can reinstall if we have an URL but no update
				$this->setLayout('reinstall');
			}
			// No download available
			else
			{
				if ($hasUpdate)
				{
					$this->messagePrefix = '_NODOWNLOAD';
				}

				$this->setLayout('noupdate');
			}
		}
		// Here we have now two options: preupdatecheck or update
		elseif ($this->getLayout() != 'update')
		{
			$this->setLayout('preupdatecheck');
		}

		// @TODO show message on normal update
		if ($this->showUploadAndUpdate || in_array($this->getLayout(), ['preupdatecheck', 'update']))
		{
			$language = Factory::getLanguage();
			$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
			$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

			Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATE_NOTICE'), 'warning');
		}

		// Get data from the model.

		// Load useful classes.
		/** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
		$model = $this->getModel();

		// Get results of pre update check evaluations
		$this->phpOptions             = $this->get('PhpOptions');
		$this->phpSettings            = $this->get('PhpSettings');
		$this->nonCoreExtensions      = $this->get('NonCoreExtensions');
		$this->nonCoreCriticalPlugins = $model->getNonCorePlugins(array('system','user','authentication','actionlog','twofactorauth'));

		$params = ComponentHelper::getParams('com_joomlaupdate');

		switch ($params->get('updatesource', 'default'))
		{
			// "Minor & Patch Release for Current version AND Next Major Release".
			case 'sts':
			case 'next':
				$this->langKey         = 'COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATES_INFO_NEXT';
				$this->updateSourceKey = Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_NEXT');
				break;

			// "Testing"
			case 'testing':
				$this->langKey         = 'COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATES_INFO_TESTING';
				$this->updateSourceKey = Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_TESTING');
				break;

			// "Custom"
			case 'custom':
				$this->langKey         = 'COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATES_INFO_CUSTOM';
				$this->updateSourceKey = Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_CUSTOM');
				break;

			/**
			 * "Minor & Patch Release for Current version (recommended and default)".
			 * The commented "case" below are for documenting where 'default' and legacy options falls
			 * case 'default':
			 * case 'lts':
			 * case 'nochange':
			 */
			default:
				$this->langKey         = 'COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATES_INFO_DEFAULT';
				$this->updateSourceKey = Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_DEFAULT');
		}

		// Remove temporary files
		$model->removePackageFiles();

		// Render the view.
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		// Set the toolbar information.
		ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'joomla install');

		if ($this->showUploadAndUpdate)
		{
			$arrow  = Factory::getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

			ToolbarHelper::link('index.php?option=com_joomlaupdate', 'JTOOLBAR_BACK', $arrow);

			ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'), 'joomla install');
		}
		elseif (!$this->selfUpdateAvailable)
		{
			ToolbarHelper::custom('update.purge', 'loop', '', 'COM_JOOMLAUPDATE_TOOLBAR_CHECK', false);
		}

		// Add toolbar buttons.
		if (Factory::getUser()->authorise('core.admin'))
		{
			ToolbarHelper::preferences('com_joomlaupdate');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');
	}

	/**
	 * Makes sure that the Joomla! Update Component Update is in the database and check if there is a new version.
	 *
	 * @return  boolean  True if there is an update else false
	 *
	 * @since   3.6.3
	 */
	private function checkForSelfUpdate()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_joomlaupdate'));
		$db->setQuery($query);

		try
		{
			// Get the component extension ID
			$joomlaUpdateComponentId = $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			// Something is wrong here!
			$joomlaUpdateComponentId = 0;
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		// Try the update only if we have an extension id
		if ($joomlaUpdateComponentId != 0)
		{
			// Always force to check for an update!
			$cache_timeout = 0;

			$updater = Updater::getInstance();
			$updater->findUpdates($joomlaUpdateComponentId, $cache_timeout, Updater::STABILITY_STABLE);

			// Fetch the update information from the database.
			$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__updates'))
				->where($db->quoteName('extension_id') . ' = :id')
				->bind(':id', $joomlaUpdateComponentId, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$joomlaUpdateComponentObject = $db->loadObject();
			}
			catch (\RuntimeException $e)
			{
				// Something is wrong here!
				$joomlaUpdateComponentObject = null;
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

			if (is_null($joomlaUpdateComponentObject))
			{
				// No Update great!
				return false;
			}

			return true;
		}
	}

	/**
	 * Returns true, if the pre update check should be displayed.
	 * This logic is not hardcoded in tmpl files, because it is
	 * used by the Hathor tmpl too.
	 *
	 * @return boolean
	 *
	 * @since 3.10.0
	 */
	public function shouldDisplayPreUpdateCheck()
	{
		return isset($this->updateInfo['object']->downloadurl->_data)
			&& !empty($this->updateInfo['hasUpdate']);
	}
}
