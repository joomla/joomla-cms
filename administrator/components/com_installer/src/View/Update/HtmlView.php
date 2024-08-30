<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Update;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper as CmsInstallerHelper;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Update View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * List of update items.
     *
     * @var array
     */
    protected $items;

    /**
     * List pagination.
     *
     * @var \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * How many updates require but are missing Download Keys
     *
     * @var   integer
     * @since 4.0.0
     */
    protected $missingDownloadKeys = 0;

    /**
     * @var boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  Template
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        // Get data from the model.
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $paths        = new \stdClass();
        $paths->first = '';

        $this->paths = &$paths;

        if (\count($this->items) === 0 && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_WARNINGS_UPDATE_NOTICE'), 'warning');
        }

        // Find if there are any updates which require but are missing a Download Key
        if (!class_exists('Joomla\Component\Installer\Administrator\Helper\InstallerHelper')) {
            require_once JPATH_COMPONENT_ADMINISTRATOR . '/Helper/InstallerHelper.php';
        }

        $mappingCallback = function ($item) {
            $dlkeyInfo                  = CmsInstallerHelper::getDownloadKey(new CMSObject($item));
            $item->isMissingDownloadKey = $dlkeyInfo['supported'] && !$dlkeyInfo['valid'];

            if ($item->isMissingDownloadKey) {
                $this->missingDownloadKeys++;
            }

            return $item;
        };
        $this->items = array_map($mappingCallback, $this->items);

        if ($this->missingDownloadKeys) {
            $url = 'index.php?option=com_installer&view=updatesites&filter[supported]=-1';
            $msg = Text::plural('COM_INSTALLER_UPDATE_MISSING_DOWNLOADKEY_LABEL_N', $this->missingDownloadKeys, $url);
            Factory::getApplication()->enqueueMessage($msg, CMSApplication::MSG_WARNING);
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $toolbar = $this->getDocument()->getToolbar();

        if (false === $this->isEmptyState) {
            $toolbar->standardButton('upload', 'COM_INSTALLER_TOOLBAR_UPDATE', 'update.update')
                ->listCheck(true)
                ->icon('icon-upload');
        }

        $toolbar->standardButton('search', 'COM_INSTALLER_TOOLBAR_FIND_UPDATES', 'update.find')
            ->listCheck(false)
            ->icon('icon-refresh');

        $toolbar->linkButton('list', 'COM_INSTALLER_TOOLBAR_MANAGE')
            ->url('index.php?option=com_installer&view=manage');

        $toolbar->divider();

        parent::addToolbar();
        $toolbar->help('Extensions:_Update');
    }
}
