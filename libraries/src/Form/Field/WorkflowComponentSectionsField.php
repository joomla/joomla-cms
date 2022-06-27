<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Workflow\WorkflowServiceInterface;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  4.0.0
 */
class WorkflowComponentSectionsField extends ComponentsField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since  4.0.0
     */
    protected $type = 'WorkflowComponentSections';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since   4.0.0
     */
    protected function getOptions()
    {
        $app       = Factory::getApplication();
        $items     = parent::getOptions();
        $options   = [];
        $options[] = HTMLHelper::_('select.option', ' ', Text::_('JNONE'));

        foreach ($items as $item) {
            if (substr($item->value, 0, 4) !== 'com_') {
                continue;
            }

            $component = $app->bootComponent($item->value);

            if (!($component instanceof WorkflowServiceInterface)) {
                continue;
            }

            foreach ($component->getWorkflowContexts() as $extension => $text) {
                $options[] = HTMLHelper::_('select.option', $extension, Text::sprintf('JWORKFLOW_FIELD_COMPONENT_SECTIONS_TEXT', $item->text, $text));
            }
        }

        return $options;
    }
}
