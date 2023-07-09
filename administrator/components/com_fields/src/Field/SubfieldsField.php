<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Subfields. Represents a list field with the options being all possible
 * custom field types, except the 'subform' custom field type.
 *
 * @since  4.0.0
 */
class SubfieldsField extends ListField
{
    /**
     * The name of this Field type.
     *
     * @var string
     *
     * @since 4.0.0
     */
    public $type = 'Subfields';

    /**
     * Configuration option for this field type to could filter the displayed custom field instances
     * by a given context. Default value empty string. If given empty string, displays all custom fields.
     *
     * @var string
     *
     * @since 4.0.0
     */
    protected $context = '';

    /**
     * Array to do a fast in-memory caching of all custom field items. Used to not bother the
     * FieldsHelper with a call every time this field is being rendered.
     *
     * @var array
     *
     * @since 4.0.0
     */
    protected static $customFieldsCache = [];

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   4.0.0
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        // Check whether we have a result for this context yet
        if (!isset(static::$customFieldsCache[$this->context])) {
            static::$customFieldsCache[$this->context] = FieldsHelper::getFields($this->context, null, false, null, true);
        }

        // Iterate over the custom fields for this context
        foreach (static::$customFieldsCache[$this->context] as $customField) {
            // Skip our own subform type. We won't have subform in subform.
            if ($customField->type == 'subform') {
                continue;
            }

            $options[] = HTMLHelper::_(
                'select.option',
                $customField->id,
                ($customField->title . ' (' . $customField->type . ')')
            );
        }

        // Sorting the fields based on the text which is displayed
        usort(
            $options,
            function ($a, $b) {
                return strcmp($a->text, $b->text);
            }
        );

        if (count($options) == 0) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_FIELDS_NO_FIELDS_TO_CREATE_SUBFORM_FIELD_WARNING'), 'warning');
        }

        return $options;
    }

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
     * @since   4.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->context = (string) $this->element['context'];
        }

        return $return;
    }
}
