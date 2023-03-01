<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a grouped list select field.
 *
 * @since  1.7.0
 */
class GroupedlistField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Groupedlist';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.groupedlist';

    /**
     * Method to get the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    protected function getGroups()
    {
        $groups = [];
        $label = 0;

        foreach ($this->element->children() as $element) {
            switch ($element->getName()) {
                // The element is an <option />
                case 'option':
                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    $disabled = (string) $element['disabled'];
                    $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                    // Create a new option object based on the <option /> element.
                    $tmp = HTMLHelper::_(
                        'select.option',
                        ($element['value']) ? (string) $element['value'] : trim((string) $element),
                        Text::alt(trim((string) $element), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                        'value',
                        'text',
                        $disabled
                    );

                    // Set some option attributes.
                    $tmp->class = (string) $element['class'];

                    // Set some JavaScript option attributes.
                    $tmp->onclick = (string) $element['onclick'];

                    // Add the option.
                    $groups[$label][] = $tmp;
                    break;

                // The element is a <group />
                case 'group':
                    // Get the group label.
                    if ($groupLabel = (string) $element['label']) {
                        $label = Text::_($groupLabel);
                    }

                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }

                    // Iterate through the children and build an array of options.
                    foreach ($element->children() as $option) {
                        // Only add <option /> elements.
                        if ($option->getName() !== 'option') {
                            continue;
                        }

                        $disabled = (string) $option['disabled'];
                        $disabled = ($disabled === 'true' || $disabled === 'disabled' || $disabled === '1');

                        // Create a new option object based on the <option /> element.
                        $tmp = HTMLHelper::_(
                            'select.option',
                            ($option['value']) ? (string) $option['value'] : Text::_(trim((string) $option)),
                            Text::_(trim((string) $option)),
                            'value',
                            'text',
                            $disabled
                        );

                        // Set some option attributes.
                        $tmp->class = (string) $option['class'];

                        // Set some JavaScript option attributes.
                        $tmp->onclick = (string) $option['onclick'];

                        // Add the option.
                        $groups[$label][] = $tmp;
                    }

                    if ($groupLabel) {
                        $label = \count($groups);
                    }
                    break;

                // Unknown element type.
                default:
                    throw new \UnexpectedValueException(sprintf('Unsupported element %s in GroupedlistField', $element->getName()), 500);
            }
        }

        reset($groups);

        return $groups;
    }

    /**
     * Method to get the field input markup fora grouped list.
     * Multiselect is enabled by using the multiple attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        // Get the field groups.
        $data['groups'] = (array) $this->getGroups();

        return $this->getRenderer($this->layout)->render($data);
    }
}
