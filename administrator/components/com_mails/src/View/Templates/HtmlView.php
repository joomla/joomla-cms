<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\View\Templates;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Mails\Administrator\Helper\MailsHelper;
use Joomla\Component\Mails\Administrator\Model\TemplatesModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View for the mail templates configuration
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * An array of installed languages
     *
     * @var  array
     */
    protected $languages;

    /**
     * Site default language
     *
     * @var \stdClass
     */
    protected $defaultLanguage;

    /**
     * The pagination object
     *
     * @var  Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function display($tpl = null)
    {
        /** @var TemplatesModel $model */
        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->languages     = $model->getLanguages();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $extensions          = $model->getExtensions();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Find and set site default language
        $defaultLanguageTag = ComponentHelper::getParams('com_languages')->get('site');

        foreach ($this->languages as $tag => $language) {
            if ($tag === $defaultLanguageTag) {
                $this->defaultLanguage = $language;
                break;
            }
        }

        foreach ($extensions as $extension) {
            MailsHelper::loadTranslationFiles($extension, $defaultLanguageTag);
        }

        $this->addToolbar();

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
        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar();
        $user    = $this->getCurrentUser();

        ToolbarHelper::title(Text::_('COM_MAILS_MAILS_TITLE'), 'envelope');

        if ($user->authorise('core.admin', 'com_mails') || $user->authorise('core.options', 'com_mails')) {
            $toolbar->preferences('com_mails');
        }

        $toolbar->help('Mail_Templates');
    }
}
