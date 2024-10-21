<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\View\Associations;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Associations\Administrator\Model\AssociationsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var   array
     *
     * @since  3.7.0
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  3.7.0
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    object
     *
     * @since  3.7.0
     */
    protected $state;

    /**
     * Selected item type properties.
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  3.7.0
     */
    public $itemType = null;

    /**
     * Main Extension Name
     *
     * @var    string
     *
     * @since  5.2.0
     */
    public $extensionName;

    /**
     * Subtype of the extension
     *
     * @var    string
     *
     * @since  5.2.0
     */
    public $typeName;

    /**
     * Supported features
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $typeSupports;

    /**
     * Fields
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $typeFields;

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
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  3.7.0
     */
    public function display($tpl = null)
    {
        /** @var AssociationsModel $model */
        $model = $this->getModel();

        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (!Associations::isEnabled()) {
            $link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . AssociationsHelper::getLanguagefilterPluginId());
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_ASSOCIATIONS_ERROR_NO_ASSOC', $link), 'warning');
        } elseif ($this->state->get('itemtype') != '' && $this->state->get('language') != '') {
            $type = null;

            [$extensionName, $typeName] = explode('.', $this->state->get('itemtype'), 2);

            $extension = AssociationsHelper::getSupportedExtension($extensionName);

            $types = $extension->get('types');

            if (\array_key_exists($typeName, $types)) {
                $type = $types[$typeName];
            }

            $this->itemType = $type;

            if (\is_null($type)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_ASSOCIATIONS_ERROR_NO_TYPE'), 'warning');
            } else {
                $this->extensionName = $extensionName;
                $this->typeName      = $typeName;
                $this->typeSupports  = [];
                $this->typeFields    = [];

                $details = $type->get('details');

                if (\array_key_exists('support', $details)) {
                    $support            = $details['support'];
                    $this->typeSupports = $support;
                }

                if (\array_key_exists('fields', $details)) {
                    $fields           = $details['fields'];
                    $this->typeFields = $fields;
                }

                // Dynamic filter form.
                // This selectors doesn't have to activate the filter bar.
                unset($this->activeFilters['itemtype']);
                unset($this->activeFilters['language']);

                // Remove filters options depending on selected type.
                if (empty($support['state'])) {
                    unset($this->activeFilters['state']);
                    $this->filterForm->removeField('state', 'filter');
                }

                if (empty($support['category'])) {
                    unset($this->activeFilters['category_id']);
                    $this->filterForm->removeField('category_id', 'filter');
                }

                if ($extensionName !== 'com_menus') {
                    unset($this->activeFilters['menutype']);
                    $this->filterForm->removeField('menutype', 'filter');
                }

                if (empty($support['level'])) {
                    unset($this->activeFilters['level']);
                    $this->filterForm->removeField('level', 'filter');
                }

                if (empty($support['acl'])) {
                    unset($this->activeFilters['access']);
                    $this->filterForm->removeField('access', 'filter');
                }

                // Add extension attribute to category filter.
                if (empty($support['catid'])) {
                    $this->filterForm->setFieldAttribute('category_id', 'extension', $extensionName, 'filter');

                    if ($this->getLayout() == 'modal') {
                        // We need to change the category filter to only show categories tagged to All or to the forced language.
                        $forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD');
                        if ($forcedLanguage) {
                            $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
                        }
                    }
                }

                $this->items      = $model->getItems();
                $this->pagination = $model->getPagination();

                $linkParameters = [
                    'layout'   => 'edit',
                    'itemtype' => $extensionName . '.' . $typeName,
                    'task'     => 'association.edit',
                ];

                $this->editUri = 'index.php?option=com_associations&view=association&' . http_build_query($linkParameters);
            }
        }

        // Check for errors.
        // @todo: 6.0 - Update Error handling
        $errors = $model->getErrors();
        if (\count($errors)) {
            throw new \Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since  3.7.0
     */
    protected function addToolbar()
    {
        $user = $this->getCurrentUser();

        if (isset($this->typeName) && isset($this->extensionName)) {
            $helper = AssociationsHelper::getExtensionHelper($this->extensionName);
            $title  = $helper->getTypeTitle($this->typeName);

            $languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

            if ($this->typeName === 'category') {
                $languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
            }

            ToolbarHelper::title(
                Text::sprintf(
                    'COM_ASSOCIATIONS_TITLE_LIST',
                    Text::_($this->extensionName),
                    Text::_($languageKey)
                ),
                'language assoc'
            );
        } else {
            ToolbarHelper::title(Text::_('COM_ASSOCIATIONS_TITLE_LIST_SELECT'), 'language assoc');
        }

        $toolbar = $this->getDocument()->getToolbar();

        if ($user->authorise('core.admin', 'com_associations') || $user->authorise('core.options', 'com_associations')) {
            if (!isset($this->typeName)) {
                $toolbar->standardButton('', 'COM_ASSOCIATIONS_PURGE', 'associations.purge')
                    ->icon('icon-purge')
                    ->listCheck(false);
                $toolbar->standardButton('', 'COM_ASSOCIATIONS_DELETE_ORPHANS', 'associations.clean')
                    ->icon('icon-refresh')
                    ->listCheck(false);
            }

            $toolbar->preferences('com_associations');
        }

        $toolbar->help('Multilingual_Associations');
    }
}
