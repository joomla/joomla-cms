<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\View\Category;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Categories\Administrator\Model\CategoryModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Categories component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Flag if an association exists
     *
     * @var  boolean
     */
    protected $assoc;

    /**
     * The actions the user is authorised to perform
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $canDo;

    /**
     * Is there a content type associated with this category alias
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $checkTags = false;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $ignore_fieldsets = [];

    /**
     * Display the view.
     *
     * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var CategoryModel $model */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $section     = $this->state->get('category.section') ? $this->state->get('category.section') . '.' : '';
        $this->canDo = ContentHelper::getActions($this->state->get('category.component'), $section . 'category', $this->item->id);
        $this->assoc = $model->getAssoc();

        if ($this->getLayout() === 'modalreturn') {
            parent::display($tpl);

            return;
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check if we have a content type for this alias
        if (!empty(TagsHelper::getTypes('objectList', [$this->state->get('category.extension') . '.category'], true))) {
            $this->checkTags = true;
        }

        $input          = Factory::getApplication()->getInput();
        $forcedLanguage = $input->get('forcedLanguage', '', 'cmd');

        $input->set('hidemainmenu', true);

        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage) {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
        }

        // Add form control fields
        $this->form
            ->addControlField('task', '')
            ->addControlField('return', $input->getBase64('return', ''))
            ->addControlField('forcedLanguage', $forcedLanguage);

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        } else {
            $this->addModalToolbar();
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
        $extension = Factory::getApplication()->getInput()->get('extension');

        $user      = $this->getCurrentUser();
        $userId    = $user->id;
        $toolbar   = $this->getDocument()->getToolbar();

        $isNew      = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $userId);

        // Avoid nonsense situation.
        if ($extension == 'com_categories') {
            return;
        }

        // The extension can be in the form com_foo.section
        $parts           = explode('.', $extension);
        $component       = $parts[0];
        $section         = (\count($parts) > 1) ? $parts[1] : null;
        $componentParams = ComponentHelper::getParams($component);

        // Need to load the menu language file as mod_menu hasn't been loaded yet.
        $lang = $this->getLanguage();
        $lang->load($component, JPATH_BASE)
            || $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

        // Get the results for each action.
        $canDo = $this->canDo;

        // If a component categories title string is present, let's use it.
        if ($lang->hasKey($component_title_key = $component . ($section ? "_$section" : '') . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE')) {
            $title = Text::_($component_title_key);
        } elseif ($lang->hasKey($component_section_key = $component . ($section ? "_$section" : ''))) {
            // Else if the component section string exists, let's use it.
            $title = Text::sprintf('COM_CATEGORIES_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT')
                . '_TITLE', $this->escape(Text::_($component_section_key)));
        } else {
            // Else use the base title
            $title = Text::_('COM_CATEGORIES_CATEGORY_BASE_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE');
        }

        // Load specific css component
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile($component);

        if ($wa->assetExists('style', $component . '.admin-categories')) {
            $wa->useStyle($component . '.admin-categories');
        } else {
            $wa->registerAndUseStyle($component . '.admin-categories', $component . '/administrator/categories.css');
        }

        // Prepare the toolbar.
        ToolbarHelper::title(
            $title,
            'folder category-' . ($isNew ? 'add' : 'edit')
                . ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-category-' . ($isNew ? 'add' : 'edit')
        );

        if ($isNew) {
            $toolbar->apply('category.apply');
            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) use ($canDo, $component) {
                    $childBar->save('category.save');
                    $childBar->save2new('category.save2new');

                    if ($canDo->get('core.create', 'com_menus.menu') && $component === 'com_content') {
                        $childBar->save('category.save2menulist', 'JTOOLBAR_SAVE_TO_MENU_AS_LIST');
                        $childBar->save('category.save2menublog', 'JTOOLBAR_SAVE_TO_MENU_AS_BLOG');
                    }
                }
            );

            $toolbar->cancel('category.cancel', 'JTOOLBAR_CANCEL');
        } else {
            // If not checked out, can save the item.
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                $toolbar->apply('category.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $canDo, $itemEditable, $component) {
                    // Can't save the record if it's checked out and editable
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save('category.save');

                        if ($canDo->get('core.create')) {
                            $childBar->save2new('category.save2new');
                        }
                    }

                    if ($canDo->get('core.create', 'com_menus.menu') && $component === 'com_content') {
                        $childBar->save('category.save2menulist', 'JTOOLBAR_SAVE_TO_MENU_AS_LIST');
                        $childBar->save('category.save2menublog', 'JTOOLBAR_SAVE_TO_MENU_AS_BLOG');
                    }

                    // If an existing item, can save to a copy.
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('category.save2copy');
                    }
                }
            );

            $toolbar->cancel('category.cancel');

            if (ComponentHelper::isEnabled('com_contenthistory') && $componentParams->get('save_history', 0) && $itemEditable) {
                $typeAlias = $extension . '.category';
                $toolbar->versions($typeAlias, $this->item->id);
            }

            if (
                Associations::isEnabled() &&
                ComponentHelper::isEnabled('com_associations') &&
                AssociationsHelper::hasSupport($component)
            ) {
                $toolbar->standardButton('contract', 'JTOOLBAR_ASSOCIATIONS', 'category.editAssociations')
                    ->icon('icon-contract')
                    ->listCheck(false);
            }
        }

        $toolbar->divider();

        // Look first in form for help key
        $ref_key = (string) $this->form->getXml()->help['key'];

        // Try with a language string
        if (!$ref_key) {
            // Compute the ref_key if it does exist in the component
            $languageKey = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT') . '_HELP_KEY';

            if ($lang->hasKey($languageKey)) {
                $ref_key = $languageKey;
            } else {
                $languageKey = 'JHELP_COMPONENTS_'
                    . strtoupper(substr($component, 4) . ($section ? "_$section" : ''))
                    . '_CATEGORY_' . ($isNew ? 'ADD' : 'EDIT');

                if ($lang->hasKey($languageKey)) {
                    $ref_key = $languageKey;
                }
            }
        }

        /*
         * Get help for the category/section view for the component by
         * -remotely searching in a URL defined in the category form
         * -remotely searching in a language defined dedicated URL: *component*_HELP_URL
         * -locally  searching in a component help file if helpURL param exists in the component and is set to ''
         * -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
         */
        $url = (string) $this->form->getXml()->help['url'];

        if (!$url) {
            if ($lang->hasKey($lang_help_url = strtoupper($component) . '_HELP_URL')) {
                $debug = $lang->setDebug(false);
                $url   = Text::_($lang_help_url);
                $lang->setDebug($debug);
            }
        }

        $toolbar->help($ref_key, $componentParams->exists('helpURL'), $url, $component);
    }

    /**
     * Add the modal toolbar.
     *
     * @return  void
     *
     * @since   5.1.0
     *
     * @throws  \Exception
     */
    protected function addModalToolbar()
    {
        $extension  = Factory::getApplication()->getInput()->get('extension');
        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $toolbar    = $this->getDocument()->getToolbar();

        // Avoid nonsense situation.
        if ($extension == 'com_categories') {
            return;
        }

        // The extension can be in the form com_foo.section
        $parts     = explode('.', $extension);
        $component = $parts[0];

        // Need to load the menu language file as mod_menu hasn't been loaded yet.
        $lang = $this->getLanguage();
        $lang->load($component, JPATH_BASE)
            || $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

        // Build the actions for new and existing records.
        $canDo = $this->canDo;

        // Load specific css component
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile($component);

        if ($wa->assetExists('style', $component . '.admin-categories')) {
            $wa->useStyle($component . '.admin-categories');
        } else {
            $wa->registerAndUseStyle($component . '.admin-categories', $component . '/administrator/categories.css');
        }

        $canCreate = $isNew;
        $canEdit   = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_user_id == $userId);

        // For new records, check the create permission.
        if ($canCreate || $canEdit) {
            $toolbar->apply('category.apply');
            $toolbar->save('category.save');
        }

        $toolbar->cancel('category.cancel');
    }
}
