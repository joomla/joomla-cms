<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal category picker.
 *
 * @since  3.1
 */
class CategoryField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Modal_Category';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Check if the value consist with id:alias, extract the id only
        if ($value && str_contains($value, ':')) {
            [$id]  = explode(':', $value, 2);
            $value = (int) $id;
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] == 'true') && \count($languages) > 2;

        // Prepare Urls
        $linkArticles = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkArticles->setQuery([
            'option'                => 'com_categories',
            'view'                  => 'categories',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkArticle = clone $linkArticles;
        $linkArticle->setVar('view', 'category');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_categories',
            'task'                  => 'categories.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkArticles->setVar('forcedLanguage', $language);
            $linkArticle->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_CATEGORIES_SELECT_A_CATEGORY') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_CATEGORIES_SELECT_A_CATEGORY');
        }

        $urlSelect = $linkArticles;
        $urlEdit   = clone $linkArticle;
        $urlEdit->setVar('task', 'category.edit');
        $urlNew    = clone $linkArticle;
        $urlNew->setVar('task', 'category.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_CATEGORIES_NEW_CATEGORY');
        $this->modalTitles['edit']    = Text::_('COM_CATEGORIES_EDIT_CATEGORY');

        $this->hint = $this->hint ?: Text::_('COM_CATEGORIES_SELECT_A_CATEGORY');

        return $result;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput1()
    {
        if ($this->element['extension']) {
            $extension = (string) $this->element['extension'];
        } else {
            $extension = (string) Factory::getApplication()->getInput()->get('extension', 'com_content');
        }

        $allowNew       = ((string) $this->element['new'] == 'true');
        $allowEdit      = ((string) $this->element['edit'] == 'true');
        $allowClear     = ((string) $this->element['clear'] != 'false');
        $allowSelect    = ((string) $this->element['select'] != 'false');
        $allowPropagate = ((string) $this->element['propagate'] == 'true');

        $languages = LanguageHelper::getContentLanguages([0, 1], false);

        // Load language.
        Factory::getLanguage()->load('com_categories', JPATH_ADMINISTRATOR);

        // The active category id field.
        $value = (int) $this->value ?: '';

        // Create the modal id.
        $modalId = 'Category_' . $this->id;

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect) {
            static $scriptSelect = null;

            if (\is_null($scriptSelect)) {
                $scriptSelect = [];
            }

            if (!isset($scriptSelect[$this->id])) {
                $wa->addInlineScript(
                    "
				window.jSelectCategory_" . $this->id . " = function (id, title, object) {
					window.processModalSelect('Category', '" . $this->id . "', id, title, '', object);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkCategories = 'index.php?option=com_categories&amp;view=categories&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1'
            . '&amp;extension=' . $extension;
        $linkCategory  = 'index.php?option=com_categories&amp;view=category&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1'
            . '&amp;extension=' . $extension;
        $modalTitle    = Text::_('COM_CATEGORIES_SELECT_A_CATEGORY');

        if (isset($this->element['language'])) {
            $linkCategories .= '&amp;forcedLanguage=' . $this->element['language'];
            $linkCategory .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle .= ' &#8212; ' . $this->element['label'];
        }

        $urlSelect = $linkCategories . '&amp;function=jSelectCategory_' . $this->id;
        $urlEdit   = $linkCategory . '&amp;task=category.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
        $urlNew    = $linkCategory . '&amp;task=category.add';

        if ($value) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $title = $db->loadResult();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $title = empty($title) ? Text::_('COM_CATEGORIES_SELECT_A_CATEGORY') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current category display field.
        $html  = '';

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

        // Select category button.
        if ($allowSelect) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalSelect' . $modalId . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }

        // New category button.
        if ($allowNew) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_new"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalNew' . $modalId . '">'
                . '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';
        }

        // Edit category button.
        if ($allowEdit) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_edit"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalEdit' . $modalId . '">'
                . '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
                . '</button>';
        }

        // Clear category button.
        if ($allowClear) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate category button
        if ($allowPropagate && \count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int) \strlen($this->element['language']);
            $callbackFunctionStem = substr("jSelectCategory_" . $this->id, 0, -$tagLength);

            $html .= '<button'
            . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
            . ' type="button"'
            . ' id="' . $this->id . '_propagate"'
            . ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
            . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
            . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
            . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '</span>';
        }

        // Select category modal.
        if ($allowSelect) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                [
                    'title'      => $modalTitle,
                    'url'        => $urlSelect,
                    'height'     => '400px',
                    'width'      => '800px',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                    'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                ]
            );
        }

        // New category modal.
        if ($allowNew) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalNew' . $modalId,
                [
                    'title'       => Text::_('COM_CATEGORIES_NEW_CATEGORY'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlNew,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'category\', \'cancel\', \'item-form\'); return false;">'
                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                            . '<button type="button" class="btn btn-primary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'category\', \'save\', \'item-form\'); return false;">'
                            . Text::_('JSAVE') . '</button>'
                            . '<button type="button" class="btn btn-success"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'category\', \'apply\', \'item-form\'); return false;">'
                            . Text::_('JAPPLY') . '</button>',
                ]
            );
        }

        // Edit category modal.
        if ($allowEdit) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalEdit' . $modalId,
                [
                    'title'       => Text::_('COM_CATEGORIES_EDIT_CATEGORY'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlEdit,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'category\', \'cancel\', \'item-form\'); return false;">'
                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                            . '<button type="button" class="btn btn-primary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'category\', \'save\', \'item-form\'); return false;">'
                            . Text::_('JSAVE') . '</button>'
                            . '<button type="button" class="btn btn-success"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'category\', \'apply\', \'item-form\'); return false;">'
                            . Text::_('JAPPLY') . '</button>',
                ]
            );
        }

        // Note: class='required' for client side validation
        $class = $this->required ? ' class="required modal-value"' : '';

        $html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars(Text::_('COM_CATEGORIES_SELECT_A_CATEGORY', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.7.0
     */
    protected function getLabel1()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getValueTitle()
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__categories'))
                    ->where($db->quoteName('id') . ' = :value')
                    ->bind(':value', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data             = parent::getLayoutData();
        $data['language'] = (string) $this->element['language'];

        return $data;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_categories');
        $layout->setClient(1);

        return $layout;
    }
}
