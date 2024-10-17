<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\FolderlistField;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Generates the list of directories  available for drag and drop upload.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.7.0
 *
 * @deprecated  5.2.0 will be removed in 7.0
 *               Use Joomla\CMS\Form\Field\FolderlistField.
 */
class UploaddirsField extends FolderlistField
{
    protected $type = 'uploaddirs';

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
     * @see     \Joomla\CMS\Form\FormField::setup()
     * @since   3.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        // Get the path in which to search for file options.
        $this->directory   = JPATH_ROOT . '/' . ComponentHelper::getParams('com_media')->get('image_path', 'images');
        $this->recursive   = true;
        $this->hideDefault = true;

        @trigger_error(
            __CLASS__ . ' is deprecated, use Joomla\CMS\Form\Field\FolderlistField instead. Will be removed in 7.0.',
            \E_USER_DEPRECATED
        );

        return $return;
    }

    /**
     * Method to get the directories options.
     *
     * @return  array  The dirs option objects.
     *
     * @since   3.7.0
     */
    public function getOptions()
    {
        return parent::getOptions();
    }

    /**
     * Method to get the field input markup for the list of directories.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput()
    {
        $html = [];

        // Get the field options.
        $options = (array) $this->getOptions();

        // Reset the non selected value to null
        if ($options[0]->value === '-1') {
            $options[0]->value = '';
        }

        // Create a regular list.
        $html[] = HTMLHelper::_('select.genericlist', $options, $this->name, 'class="form-select"', 'value', 'text', $this->value, $this->id);

        return implode($html);
    }
}
