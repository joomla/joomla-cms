<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.checkboxes
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\Checkboxes\Extension;

use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Checkboxes Plugin
 *
 * @since  3.7.0
 */
final class Checkboxes extends FieldsListPlugin
{
    /**
     * Before prepares the field value.
     *
     * @param   string     $context  The context.
     * @param   \stdclass  $item     The item.
     * @param   \stdclass  $field    The field.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onCustomFieldsBeforePrepareField($context, $item, $field)
    {
        if (!$this->getApplication()->isClient('api')) {
            return;
        }

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $field->apivalue = [];

        $options = $this->getOptionsFromField($field);

        if (empty($field->value)) {
            return;
        }

        if (is_array($field->value)) {
            foreach ($field->value as $key => $value) {
                $field->apivalue[$value] = $options[$value];
            }
        } else {
            $field->apivalue[$field->value] = $options[$field->value];
        }
    }
}
