<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Field;

use Joomla\CMS\Form\Field\FolderlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Generates the list of directories available for template snippets.
 *
 * @since       4.1.0
 */
class TemplatesListField extends FolderlistField
{
    protected $type = 'templatesList';

    /**
     * Method to attach a JForm object to the field.
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
     * @since   4.1.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        // Set some defaults.
        $this->recursive   = true;
        $this->hideDefault = true;
        $this->exclude     = 'system';
        $this->hideNone    = true;

        return $return;
    }

    /**
     * Method to get the directories options.
     *
     * @return  array  The dirs option objects.
     *
     * @since   4.1.0
     */
    public function getOptions()
    {
        $def         = new \stdClass();
        $def->value  = '';
        $def->text   = Text::_('JOPTION_DO_NOT_USE');
        $options     = [0 => $def];
        $directories = [JPATH_ROOT . '/templates', JPATH_ROOT . '/media/templates/site'];

        foreach ($directories as $directory) {
            $this->directory = $directory;
            $options         = array_merge($options, parent::getOptions());
        }

        return $options;
    }

    /**
     * Method to get the field input markup for the list of directories.
     *
     * @return  string  The field input markup.
     *
     * @since   4.1.0
     */
    protected function getInput()
    {
        return HTMLHelper::_(
            'select.genericlist',
            (array) $this->getOptions(),
            $this->name,
            'class="form-select"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
