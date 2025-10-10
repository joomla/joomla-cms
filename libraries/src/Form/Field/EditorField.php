<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A textarea field for content creation
 *
 * @see    JEditor
 * @since  1.6
 */
class EditorField extends TextareaField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'Editor';

    /**
     * The Editor object.
     *
     * @var    Editor
     * @since  1.6
     */
    protected $editor;

    /**
     * The height of the editor.
     *
     * @var    string
     * @since  3.2
     */
    protected $height;

    /**
     * The width of the editor.
     *
     * @var    string
     * @since  3.2
     */
    protected $width;

    /**
     * The assetField of the editor.
     *
     * @var    string
     * @since  3.2
     */
    protected $assetField;

    /**
     * The authorField of the editor.
     *
     * @var    string
     * @since  3.2
     */
    protected $authorField;

    /**
     * The asset of the editor.
     *
     * @var    string
     * @since  3.2
     */
    protected $asset;

    /**
     * The buttons of the editor.
     *
     * @var    mixed
     * @since  3.2
     */
    protected $buttons;

    /**
     * The hide of the editor.
     *
     * @var    string[]
     * @since  3.2
     */
    protected $hide;

    /**
     * The editorType of the editor.
     *
     * @var    string[]
     * @since  3.2
     */
    protected $editorType;

    /**
     * The parent class of the field
     *
     * @var  string
     * @since 4.0.0
     */
    protected $parentclass;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name) {
            case 'height':
            case 'width':
            case 'assetField':
            case 'authorField':
            case 'asset':
            case 'buttons':
            case 'hide':
            case 'editorType':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'height':
            case 'width':
            case 'assetField':
            case 'authorField':
            case 'asset':
                $this->$name = (string) $value;
                break;

            case 'buttons':
                $value = (string) $value;

                if ($value === 'true' || $value === 'yes' || $value === '1') {
                    $this->buttons = true;
                } elseif ($value === 'false' || $value === 'no' || $value === '0') {
                    $this->buttons = false;
                } else {
                    $this->buttons = explode(',', $value);
                }
                break;

            case 'hide':
                $value      = (string) $value;
                $this->hide = $value ? explode(',', $value) : [];
                break;

            case 'editorType':
                // Can be in the form of: editor="desired|alternative".
                $this->editorType  = explode('|', trim((string) $value));
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            $this->height      = $this->element['height'] ? (string) $this->element['height'] : '500';
            $this->width       = $this->element['width'] ? (string) $this->element['width'] : '100%';
            $this->assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
            $this->authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
            $this->asset       = $this->form->getValue($this->assetField) ?: (string) $this->element['asset_id'];

            $buttons    = (string) $this->element['buttons'];
            $hide       = (string) $this->element['hide'];
            $editorType = (string) $this->element['editor'];

            if ($buttons === 'true' || $buttons === 'yes' || $buttons === '1') {
                $this->buttons = true;
            } elseif ($buttons === 'false' || $buttons === 'no' || $buttons === '0') {
                $this->buttons = false;
            } else {
                $this->buttons = !empty($hide) ? explode(',', $buttons) : [];
            }

            $this->hide        = !empty($hide) ? explode(',', (string) $this->element['hide']) : [];
            $this->editorType  = !empty($editorType) ? explode('|', trim($editorType)) : [];
        }

        return $result;
    }

    /**
     * Method to get the field input markup for the editor area
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        // Get an editor object.
        $editor = $this->getEditor();
        $params = [
            'autofocus' => $this->autofocus,
            'readonly'  => $this->readonly || $this->disabled,
            'syntax'    => (string) $this->element['syntax'],
        ];

        return $editor->display(
            $this->name,
            htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'),
            $this->width,
            $this->height,
            $this->columns,
            $this->rows,
            $this->buttons ? (\is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false,
            $this->id,
            $this->asset,
            $this->form->getValue($this->authorField),
            $params
        );
    }

    /**
     * Method to get an Editor object based on the form field.
     *
     * @return  Editor  The Editor object.
     *
     * @since   1.6
     */
    protected function getEditor()
    {
        // Only create the editor if it is not already created.
        if (empty($this->editor)) {
            $editor = null;

            if ($this->editorType) {
                // Get the list of editor types.
                $types = $this->editorType;

                // Get the database object.
                $db = $this->getDatabase();

                // Build the query.
                $query = $db->createQuery()
                    ->select($db->quoteName('element'))
                    ->from($db->quoteName('#__extensions'))
                    ->where(
                        [
                            $db->quoteName('element') . ' = :editor',
                            $db->quoteName('folder') . ' = ' . $db->quote('editors'),
                            $db->quoteName('enabled') . ' = 1',
                        ]
                    );

                // Declare variable before binding.
                $element = '';
                $query->bind(':editor', $element);
                $query->setLimit(1);

                // Iterate over the types looking for an existing editor.
                foreach ($types as $element) {
                    // Check if the editor exists.
                    $db->setQuery($query);
                    $editor = $db->loadResult();

                    // If an editor was found stop looking.
                    if ($editor) {
                        break;
                    }
                }
            }

            // Create the JEditor instance based on the given editor.
            if ($editor === null) {
                $editor = Factory::getApplication()->get('editor');
            }

            $this->editor = Editor::getInstance($editor);
        }

        return $this->editor;
    }
}
