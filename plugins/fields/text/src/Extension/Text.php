<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.text
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Text\Extension;

use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Text Plugin
 *
 * @since  3.7.0
 */
final class Text extends FieldsPlugin implements SubscriberInterface
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     * Sets up the server-side and the client-side validation of the field.
     *
     * @param   \stdClass    $field   The field.
     * @param   \DOMElement  $parent  The field node parent.
     * @param   Form         $form    The form.
     *
     * @return  ?\DOMElement
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        $validate = $fieldNode->getAttribute('validate');

        switch ($validate) {
            case 'regex':
                $regex = $fieldNode->getAttribute('validate_regex');

                if ($regex === '') {
                    $fieldNode->removeAttribute('validate');
                    break;
                }

                // Client-side: the browser and the form validator match the whole value against the pattern
                $fieldNode->setAttribute('pattern', $regex);

                // Server-side: match the whole value as well and accept an empty value for optional fields
                $fieldNode->setAttribute('validate_regex', '^(?:' . $regex . ')$' . ($field->required ? '' : '|^$'));
                break;

            case 'equals':
            case 'notequals':
                $compareField = $fieldNode->getAttribute('field');

                if ($compareField === '') {
                    $fieldNode->removeAttribute('validate');
                    break;
                }

                // Client-side: handled by the "equals" and "notequals" handlers of the form validator
                $fieldNode->setAttribute('class', trim($fieldNode->getAttribute('class') . ' validate-' . $validate));
                $fieldNode->setAttribute('data-compare-field', $compareField);

                // The notequals rule ignores the group of the field, so it needs the full path of the field to compare to
                if ($validate === 'notequals') {
                    $groups = [];

                    for ($node = $parent; $node instanceof \DOMElement; $node = $node->parentNode) {
                        if ($node->nodeName === 'fields' && $node->getAttribute('name') !== '') {
                            array_unshift($groups, $node->getAttribute('name'));
                        }
                    }

                    $groups[] = $compareField;
                    $fieldNode->setAttribute('field', implode('.', $groups));
                }

                break;

            default:
                $fieldNode->removeAttribute('validate');
                $fieldNode->removeAttribute('message');

                return $fieldNode;
        }

        // Show the custom message for a failed validation on the client-side as well
        if ($fieldNode->getAttribute('validate') && $fieldNode->getAttribute('message') !== '') {
            $fieldNode->setAttribute('validationtext', $fieldNode->getAttribute('message'));
        }

        return $fieldNode;
    }
}
