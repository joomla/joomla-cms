<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.url
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Url\Extension;

use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Url Plugin
 *
 * @since  3.7.0
 */
final class Url extends FieldsPlugin implements SubscriberInterface
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   \stdClass    $field   The field.
     * @param   \DOMElement  $parent  The field node parent.
     * @param   Form         $form    The form.
     *
     * @return  ?\DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        // If "mailto" is configured as the only allowed scheme, the field holds a plain e-mail
        // address: the mailto: prefix is added automatically by the display layout
        // (tmpl/url.php), the user never types it. Validate the raw input as an e-mail address
        // and skip the generic URL filter, which would otherwise force a http(s) scheme onto the
        // value or resolve it as a path relative to the site root. See GH #37029.
        $schemes = (array) $field->fieldparams->get('schemes', []);

        if (\count($schemes) === 1 && reset($schemes) === 'mailto') {
            $fieldNode->setAttribute('validate', 'email');
            $fieldNode->setAttribute('filter', 'raw');
        } else {
            $fieldNode->setAttribute('validate', 'url');
        }

        if (! $fieldNode->getAttribute('relative')) {
            $fieldNode->removeAttribute('relative');
        }

        return $fieldNode;
    }
}
