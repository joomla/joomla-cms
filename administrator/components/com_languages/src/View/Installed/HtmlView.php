<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Installed;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Languages\Administrator\Model\InstalledModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Displays a list of the installed languages.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Option (component) name
     *
     * @var string
     */
    protected $option = null;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * Languages information
     *
     * @var array
     */
    protected $rows = null;

    /**
     * The model state
     *
     * @var   \Joomla\Registry\Registry
     *
     * @since  4.0.0
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  4.0.0
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  4.0.0
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var InstalledModel $model */
        $model = $this->getModel();

        $this->option        = $model->getOption();
        $this->pagination    = $model->getPagination();
        $this->rows          = $model->getData();
        $this->total         = $model->getTotal();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

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
        $canDo   = ContentHelper::getActions('com_languages');
        $toolbar = $this->getDocument()->getToolbar();

        if ((int) $this->state->get('client_id') === 1) {
            ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_INSTALLED_ADMIN_TITLE'), 'comments langmanager');
        } else {
            ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_INSTALLED_SITE_TITLE'), 'comments langmanager');
        }

        if ($canDo->get('core.edit.state')) {
            $toolbar->makeDefault('installed.setDefault');
            $toolbar->divider();
        }

        if ($canDo->get('core.admin')) {
            // Switch administrator language
            if ($this->state->get('client_id', 0) == 1) {
                $toolbar->standardButton('switch', 'COM_LANGUAGES_SWITCH_ADMIN', 'installed.switchadminlanguage')
                    ->icon('icon-refresh')
                    ->listCheck(true);
                $toolbar->divider();
            }

            $toolbar->link('COM_LANGUAGES_INSTALL', 'index.php?option=com_installer&view=languages')
                ->icon('icon-upload');
            $toolbar->divider();

            $toolbar->preferences('com_languages');
            $toolbar->divider();
        }

        $toolbar->help('Languages:_Installed');
    }
}
