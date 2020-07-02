<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
use Joomla\Component\Joomlaupdate\Administrator\Helper\Select as JoomlaupdateHelperSelect;
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
	 * The form field for the extraction select
	 *
	 * @var    string
	 *
	 * @since  3.6.0
	 */
	protected $methodSelect = null;

	/**
	 * The form field for the upload select
	 *
	 * @var   string
	 *
	 * @since  3.6.0
	 */
	protected $methodSelectUpload = null;

	/**
	 * PHP options.
	 *
	 * @var   array  Array of PHP config options
	 *
	 * @since 4.0.0
	 */
	protected $phpOptions = null;

	/**
	 * PHP settings.
	 *
	 * @var   array  Array of PHP settings
	 *
	 * @since 4.0.0
	 */
	protected $phpSettings = null;

	/**
	 * Non Core Extensions.
	 *
	 * @var   array  Array of Non-Core-Extensions
	 *
	 * @since 4.0.0
	 */
	protected $nonCoreExtensions = null;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  4.0.0
	 */
	public $state;

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
		// Get data from the model.
		$this->state = $this->get('State');

		// Load useful classes.
		/** @var \Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel $model */
		$model = $this->getModel();
		$this->loadHelper('select');

		// Assign view variables.
		$this->ftp     = $model->getFTPOptions();
		$defaultMethod = $this->ftp['enabled'] ? 'hybrid' : 'direct';

		$this->updateInfo         = $model->getUpdateInformation();
		$this->methodSelect       = JoomlaupdateHelperSelect::getMethods($defaultMethod);
		$this->methodSelectUpload = JoomlaupdateHelperSelect::getMethods($defaultMethod, 'method', 'upload_method');

		// Get results of pre update check evaluations
		$this->phpOptions        = $model->getPhpOptions();
		$this->phpSettings       = $model->getPhpSettings();
		$this->nonCoreExtensions = $model->getNonCoreExtensions();

		// Set the toolbar information.
		ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'joomla install');
		ToolbarHelper::custom('update.purge', 'loop', 'loop', 'COM_JOOMLAUPDATE_TOOLBAR_CHECK', false);

		// Add toolbar buttons.
		if (Factory::getUser()->authorise('core.admin'))
		{
			ToolbarHelper::preferences('com_joomlaupdate');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');

		if (!is_null($this->updateInfo['object']))
		{
			// Show the message if an update is found.
			Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATE_NOTICE'), 'warning');
		}

		$this->ftpFieldsDisplay = $this->ftp['enabled'] ? '' : 'style = "display: none"';
		$params                 = ComponentHelper::getParams('com_joomlaupdate');

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

		$this->warnings = array();
		/** @var \Joomla\Component\Installer\Administrator\Model\WarningsModel $warningsModel */
		$warningsModel = $this->getModel('warnings');

		if (is_object($warningsModel) && $warningsModel instanceof \Joomla\CMS\MVC\Model\BaseDatabaseModel)
		{
			$language = Factory::getLanguage();
			$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
			$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

			$this->warnings = $warningsModel->getItems();
		}

		$this->selfUpdate = $this->checkForSelfUpdate();

		// Only Super Users have access to the Update & Install for obvious security reasons
		$this->showUploadAndUpdate = Factory::getUser()->authorise('core.admin');

		// Remove temporary files
		$model->removePackageFiles();

		// Render the view.
		parent::display($tpl);
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
			// Allways force to check for an update!
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
	 * @since 4.0.0
	 */
	public function shouldDisplayPreUpdateCheck()
	{
		return isset($this->updateInfo['object']->downloadurl->_data)
			&& $this->getModel()->isDatabaseTypeSupported()
			&& $this->getModel()->isPhpVersionSupported();
	}
}
