<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

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
     * @var    \Joomla\CMS\Object\CMSObject
     *
     * @since  4.0.0
     */
    protected $state;

    /**
     * Flag if the update component itself has to be updated
     *
     * @var boolean  True when update is available otherwise false
     *
     * @since 4.0.0
     */
    protected $selfUpdateAvailable = false;

    /**
     * The default admin template for the major version of Joomla that should be used when
     * upgrading to the next major version of Joomla
     *
     * @var string
     *
     * @since 4.0.0
     */
    protected $defaultBackendTemplate = 'atum';

    /**
     * Flag if default backend template is being used
     *
     * @var boolean  True when default backend template is being used
     *
     * @since 4.0.0
     */
    protected $isDefaultBackendTemplate = false;

    /**
     * A special prefix used for the emptystate layout variable
     *
     * @var string  The prefix
     *
     * @since 4.0.0
     */
    protected $messagePrefix = '';

    /**
     * List of non core critical plugins
     *
     * @var    \stdClass[]
     * @since  4.0.0
     */
    protected $nonCoreCriticalPlugins = [];

    /**
     * Should I disable the confirmation checkbox for pre-update extension version checks?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $noVersionCheck = false;

    /**
     * Should I disable the confirmation checkbox for taking a backup before updating?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $noBackupCheck = false;

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
        $this->selfUpdateAvailable = $this->get('CheckForSelfUpdate');

        // Get results of pre update check evaluations
        $model                          = $this->getModel();
        $this->phpOptions               = $this->get('PhpOptions');
        $this->phpSettings              = $this->get('PhpSettings');
        $this->nonCoreExtensions        = $this->get('NonCoreExtensions');
        $this->isDefaultBackendTemplate = (bool) $model->isTemplateActive($this->defaultBackendTemplate);
        $nextMajorVersion               = Version::MAJOR_VERSION + 1;

        // The critical plugins check is only available for major updates.
        if (version_compare($this->updateInfo['latest'], (string) $nextMajorVersion, '>=')) {
            $this->nonCoreCriticalPlugins = $this->get('NonCorePlugins');
        }

        // Set to true if a required PHP option is not ok
        $isCritical = false;

        foreach ($this->phpOptions as $option) {
            if (!$option->state) {
                $isCritical = true;
                break;
            }
        }

        $this->state = $this->get('State');

        $hasUpdate = !empty($this->updateInfo['hasUpdate']);
        $hasDownload = isset($this->updateInfo['object']->downloadurl->_data);

        // Fresh update, show it
        if ($this->getLayout() == 'complete') {
            // Complete message, nothing to do here
        } elseif ($this->selfUpdateAvailable) {
            // There is an update for the updater itself. So we have to update it first
            $this->setLayout('selfupdate');
        } elseif (!$hasDownload || !$hasUpdate) {
            // Could be that we have a download file but no update, so we offer a re-install
            if ($hasDownload) {
                // We can reinstall if we have a URL but no update
                $this->setLayout('reinstall');
            } else {
                // No download available
                if ($hasUpdate) {
                    $this->messagePrefix = '_NODOWNLOAD';
                }

                $this->setLayout('noupdate');
            }
        } elseif ($this->getLayout() != 'update' && ($isCritical || $this->shouldDisplayPreUpdateCheck())) {
            // Here we have now two options: preupdatecheck or update
            $this->setLayout('preupdatecheck');
        } else {
            $this->setLayout('update');
        }

        if (in_array($this->getLayout(), ['preupdatecheck', 'update', 'upload'])) {
            $language = Factory::getLanguage();
            $language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
            $language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

            Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATE_NOTICE'), 'warning');
        }

        $params = ComponentHelper::getParams('com_joomlaupdate');

        switch ($params->get('updatesource', 'default')) {
            // "Minor & Patch Release for Current version AND Next Major Release".
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
             * case 'sts':
             * case 'lts':
             * case 'nochange':
             */
            default:
                $this->langKey         = 'COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATES_INFO_DEFAULT';
                $this->updateSourceKey = Text::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_DEFAULT');
        }

        $this->noVersionCheck = $params->get('versioncheck', 1) == 0;
        $this->noBackupCheck  = $params->get('backupcheck', 1) == 0;

        // Remove temporary files
        $this->getModel()->removePackageFiles();

        $this->addToolbar();

        // Render the view.
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function addToolbar()
    {
        // Set the toolbar information.
        ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'joomla install');

        if (in_array($this->getLayout(), ['update', 'complete'])) {
            $arrow = Factory::getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

            ToolbarHelper::link('index.php?option=com_joomlaupdate', 'JTOOLBAR_BACK', $arrow);

            ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'), 'joomla install');
        } elseif (!$this->selfUpdateAvailable) {
            ToolbarHelper::custom('update.purge', 'loop', '', 'COM_JOOMLAUPDATE_TOOLBAR_CHECK', false);
        }

        // Add toolbar buttons.
        if ($this->getCurrentUser()->authorise('core.admin')) {
            ToolbarHelper::preferences('com_joomlaupdate');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('Joomla_Update');
    }

    /**
     * Returns true, if the pre update check should be displayed.
     *
     * @return boolean
     *
     * @since 3.10.0
     */
    public function shouldDisplayPreUpdateCheck()
    {
        // When the download URL is not found there is no core upgrade path
        if (!isset($this->updateInfo['object']->downloadurl->_data)) {
            return false;
        }

        $nextMinor = Version::MAJOR_VERSION . '.' . (Version::MINOR_VERSION + 1);

        // Show only when we found a download URL, we have an update and when we update to the next minor or greater.
        return $this->updateInfo['hasUpdate']
            && version_compare($this->updateInfo['latest'], $nextMinor, '>=');
    }
}
