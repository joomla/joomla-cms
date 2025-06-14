<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\View\Association;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Associations\Administrator\Model\AssociationModel;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

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
     * @var    array
     *
     * @since  3.7.0
     */
    protected $items = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     *
     * @since  3.7.0
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  3.7.0
     */
    protected $state;

    /**
     * Selected item type properties.
     *
     * @var    Registry
     *
     * @since  3.7.0
     */
    protected $itemType;

    /**
     * The application
     *
     * @var    AdministratorApplication
     * @since  3.7.0
     */
    protected $app;

    /**
     * The ID of the reference language
     *
     * @var    integer
     * @since  3.7.0
     */
    protected $referenceId = 0;

    /**
     * The type name
     *
     * @var    string
     * @since  3.7.0
     */
    protected $typeName = '';

    /**
     * The reference language
     *
     * @var    string
     * @since  3.7.0
     */
    protected $referenceLanguage = '';

    /**
     * The title of the reference language
     *
     * @var    string
     * @since  3.7.0
     */
    protected $referenceTitle = '';

    /**
     * The value of the reference title
     *
     * @var    string
     * @since  3.7.0
     */
    protected $referenceTitleValue = '';

    /**
     * The URL to the edit screen
     *
     * @var    string
     * @since  3.7.0
     */
    protected $editUri = '';

    /**
     * The ID of the target field
     *
     * @var    string
     * @since  3.7.0
     */
    protected $targetId = '';

    /**
     * The target language
     *
     * @var    string
     * @since  3.7.0
     */
    protected $targetLanguage = '';

    /**
     * The source of the target field
     *
     * @var    string
     * @since  3.7.0
     */
    protected $defaultTargetSrc = '';

    /**
     * The action to perform for the target field
     *
     * @var    string
     * @since  3.7.0
     */
    protected $targetAction = '';

    /**
     * The title of the target field
     *
     * @var    string
     * @since  3.7.0
     */
    protected $targetTitle = '';

    /**
     * The edit form
     *
     * @var    Form
     * @since  3.7.0
     */
    protected $form;

    /**
     * Set if the option is set to save as copy
     *
     * @var    boolean
     * @since  3.7.0
     */
    private $save2copy = false;

    /**
     * The type of language
     *
     * @var    Registry
     * @since  3.7.0
     */
    private $type;

    /**
     * The supported types
     *
     * @var    array
     * @since  3.7.0
     */
    private $typeSupports = [];

    /**
     * The extension name
     *
     * @var    string
     * @since  3.7.0
     */
    private $extensionName = '';

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.7.0
     *
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        /** @var AssociationModel $model */
        $model = $this->getModel();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->app  = Factory::getApplication();
        $this->form = $model->getForm();
        /** @var Input $input */
        $input             = $this->app->getInput();
        $this->referenceId = $input->get('id', 0, 'int');

        [$extensionName, $typeName] = explode('.', $input->get('itemtype', '', 'string'), 2);

        /** @var Registry $extension */
        $extension = AssociationsHelper::getSupportedExtension($extensionName);
        $types     = $extension->get('types');

        if (\array_key_exists($typeName, $types)) {
            $this->type         = $types[$typeName];
            $this->typeSupports = [];
            $details            = $this->type->get('details');
            $this->save2copy    = false;

            if (\array_key_exists('support', $details)) {
                $support            = $details['support'];
                $this->typeSupports = $support;
            }

            if (!empty($this->typeSupports['save2copy'])) {
                $this->save2copy = true;
            }
        }

        $this->extensionName = $extensionName;
        $this->typeName      = $typeName;
        $this->itemType      = $extensionName . '.' . $typeName;

        $languageField = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'language');
        $referenceId   = $input->get('id', 0, 'int');
        $reference     = ArrayHelper::fromObject(AssociationsHelper::getItem($extensionName, $typeName, $referenceId));

        $this->referenceLanguage   = $reference[$languageField];
        $this->referenceTitle      = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');
        $this->referenceTitleValue = $reference[$this->referenceTitle];

        // Check for special case category
        $typeNameExploded = explode('.', $typeName);

        if (array_pop($typeNameExploded) === 'category') {
            $this->typeName = 'category';

            if ($typeNameExploded) {
                $extensionName .= '.' . implode('.', $typeNameExploded);
            }

            $options = [
                'option'    => 'com_categories',
                'view'      => 'category',
                'extension' => $extensionName,
                'tmpl'      => 'component',
            ];
        } else {
            $options = [
                'option'    => $extensionName,
                'view'      => $typeName,
                'extension' => $extensionName,
                'tmpl'      => 'component',
            ];
        }

        // Reference and target edit links.
        $this->editUri = 'index.php?' . http_build_query($options);

        // Get target language.
        $this->targetId         = '0';
        $this->targetLanguage   = '';
        $this->defaultTargetSrc = '';
        $this->targetAction     = '';
        $this->targetTitle      = '';

        if ($target = $input->get('target', '', 'string')) {
            $matches              = preg_split("#[\:]+#", $target);
            $this->targetAction   = $matches[2];
            $this->targetId       = $matches[1];
            $this->targetLanguage = $matches[0];
            $this->targetTitle    = AssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');
            $task                 = $typeName . '.' . $this->targetAction;

            /**
             * Let's put the target src into a variable to use in the javascript code
             * to avoid race conditions when the reference iframe loads.
             */
            $this->getDocument()->addScriptOptions('targetSrc', Route::_($this->editUri . '&task=' . $task . '&id=' . (int) $this->targetId));
            $this->form->setValue('itemlanguage', '', $this->targetLanguage . ':' . $this->targetId . ':' . $this->targetAction);
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
     *
     * @throws \Exception
     */
    protected function addToolbar(): void
    {
        // Hide main menu.
        $this->app->getInput()->set('hidemainmenu', 1);

        $helper = AssociationsHelper::getExtensionHelper($this->extensionName);
        $title  = $helper->getTypeTitle($this->typeName);

        $languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

        if ($this->typeName === 'category') {
            $languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
        }

        ToolbarHelper::title(
            Text::sprintf(
                'COM_ASSOCIATIONS_TITLE_EDIT',
                Text::_($this->extensionName),
                Text::_($languageKey)
            ),
            'language assoc'
        );

        $toolbar = $this->getDocument()->getToolbar();
        $toolbar->customButton('reference')
            ->html('<joomla-toolbar-button><button onclick="Joomla.submitbutton(\'reference\')" '
            . 'class="btn btn-success"><span class="icon-save" aria-hidden="true"></span>'
            . Text::_('COM_ASSOCIATIONS_SAVE_REFERENCE') . '</button></joomla-toolbar-button>');

        $toolbar->customButton('target')
            ->html('<joomla-toolbar-button id="toolbar-target"><button onclick="Joomla.submitbutton(\'target\')" '
            . 'class="btn btn-success"><span class="icon-save" aria-hidden="true"></span>'
            . Text::_('COM_ASSOCIATIONS_SAVE_TARGET') . '</button></joomla-toolbar-button>');

        if ($this->typeName === 'category' || $this->extensionName === 'com_menus' || $this->save2copy === true) {
            $toolbar->standardButton('copy', 'COM_ASSOCIATIONS_COPY_REFERENCE', 'copy')
                ->icon('icon-copy')
                ->listCheck(false);
        }

        $toolbar->cancel('association.cancel');
        $toolbar->help('Multilingual_Associations:_Edit');
    }
}
