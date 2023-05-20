<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Supports a value from an external table
 *
 * @since  4.0.0
 */
class ForeignKeyField extends ListField
{
    /**
     * The ForeignKey field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'foreignkey';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * The translate.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected bool $translate = true;

    protected bool $header = false;

    private $input_type;

    private $table;

    private $key_field;

    private $value_field;

    private $option_key_field;

    private $option_value_field;

    private $condition;

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function processQuery(): string
    {
        // Type of input the field shows
        $this->input_type = $this->getAttribute('input_type');

        // Database Table
        $this->table = $this->getAttribute('table');

        // The field that the field will save on the database
        $this->key_field = (string)$this->getAttribute('key_field');

        // The column that the field shows in the input
        $this->value_field = (string)$this->getAttribute('value_field');

        // The option field that the field will save on the database
        $this->option_key_field = (string)$this->getAttribute('option_key_field');

        // The option value that the field shows in the input
        $this->option_value_field = (string)$this->getAttribute('option_value_field');

        // Flag to identify if the fk_value is multiple
        $this->value_multiple = (int)$this->getAttribute('value_multiple', 0);

        $this->required = (string)$this->getAttribute('required', 0);

        // Flag to identify if the fk_value hides the trashed items
        $this->hideTrashed = (int)$this->getAttribute('hide_trashed', 0);

        // Flag to identify if the fk_value hides the unpublished items
        $this->hideUnpublished = (int)$this->getAttribute('hide_unpublished', 0);

        // Flag to identify if the fk_value hides the published items
        $this->hidePublished = (int)$this->getAttribute('hide_published', 0);

        // Flag to identify if the fk_value hides the archived items
        $this->hideArchived = (int)$this->getAttribute('hide_archived', 0);

        // Flag to identify if the fk has default order
        $this->fk_ordering = (string)$this->getAttribute('fk_ordering');

        // The where SQL for foreignkey
        $this->condition = (string)$this->getAttribute('condition');

        // Flag for translate options
        $this->translate = (bool)$this->getAttribute('translate');

        // Initialize variables.
        $html     = '';
        $fk_value = '';

        // Load all the field options
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Support for multiple fields on fk_values
        if ($this->value_multiple == 1) {
            // Get the fields for multiple value
            $this->value_fields = (string)$this->getAttribute('value_field_multiple');
            $this->value_fields = explode(',', $this->value_fields);
            $this->separator    = (string)$this->getAttribute('separator');

            $fk_value = ' CONCAT(';

            foreach ($this->value_fields as $field) {
                $fk_value .= $db->quoteName($field) . ', \'' . $this->separator . '\', ';
            }

            $fk_value = substr($fk_value, 0, -(strlen($this->separator) + 6));
            $fk_value .= ') AS ' . $db->quoteName($this->value_field);
        } else {
            $fk_value = $db->quoteName($this->value_field);
        }

        $query
            ->select(
                [
                    $db->quoteName($this->key_field),
                    $fk_value,
                ]
            )
            ->from($this->table);

        if ($this->hideTrashed) {
            $query->where($db->quoteName('state') . ' != -2');
        }

        if ($this->hideUnpublished) {
            $query->where($db->quoteName('state') . ' != 0');
        }

        if ($this->hidePublished) {
            $query->where($db->quoteName('state') . ' != 1');
        }

        if ($this->hideArchived) {
            $query->where($db->quoteName('state') . ' != 2');
        }

        if ($this->fk_ordering) {
            $query->order($this->fk_ordering);
        }

        if ($this->condition) {
            $query->where($this->condition);
        }


        return $query;
    }
}
