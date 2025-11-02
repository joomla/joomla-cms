<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\View\Upload;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\Model\WarningsModel;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Update's Update View
 *
 * @since  3.6.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array with the Joomla! update information.
     *
     * @var    array
     *
     * @since  4.0.0
     */
    protected $updateInfo = null;

    /**
     * Flag if the update component itself has to be updated
     *
     * @var boolean  True when update is available otherwise false
     *
     * @since 4.0.0
     */
    protected $selfUpdateAvailable = false;

    /**
     * Warnings for the upload update
     *
     * @var array  An array of warnings which could prevent the upload update
     *
     * @since 4.0.0
     */
    protected $warnings = [];

    /**
     * Should I disable the confirmation checkbox for taking a backup before updating?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $noBackupCheck = false;

    /**
     * Renders the view.
     *
     * @param   string  $tpl  Template name.
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function display($tpl = null)
    {
        /** @var UpdateModel $model */
        $model = $this->getModel();

        // Load com_installer's language
        $language = $this->getLanguage();
        $language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
        $language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

        $this->updateInfo          = $model->getUpdateInformation();
        $this->selfUpdateAvailable = $model->getCheckForSelfUpdate();

        if ($this->getLayout() !== 'captive') {
            /** @var WarningsModel $warningsModel */
            $warningsModel  = $this->getModel('warnings');
            $this->warnings = $warningsModel->getItems();
        }

        $params               = ComponentHelper::getParams('com_joomlaupdate');
        $this->noBackupCheck  = $params->get('backupcheck', 1) == 0;

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
        ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'sync install');

        $arrow = $this->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
        ToolbarHelper::link('index.php?option=com_joomlaupdate&' . ($this->getLayout() == 'captive' ? 'view=upload' : ''), 'JTOOLBAR_BACK', $arrow);
        ToolbarHelper::divider();
        ToolbarHelper::help('Joomla_Update');
    }
}
