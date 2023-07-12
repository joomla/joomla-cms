<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Workflow\WorkflowServiceInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields Contexts
 *
 * @since  4.0.0
 */
class WorkflowcontextsField extends ListField
{
    /**
     * Type of the field
     *
     * @var    string
     */
    public $type = 'Workflowcontexts';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        if (count($this->getOptions()) < 2) {
            $this->layout = 'joomla.form.field.hidden';
        }

        return parent::getInput();
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   4.0.0
     */
    protected function getOptions()
    {
        $parts = explode('.', $this->value);

        $component = Factory::getApplication()->bootComponent($parts[0]);

        if ($component instanceof WorkflowServiceInterface) {
            return $component->getWorkflowContexts();
        }

        return [];
    }
}
