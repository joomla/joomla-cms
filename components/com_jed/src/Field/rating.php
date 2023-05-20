<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

use function is_array;
use function is_int;
use function is_object;
use function is_string;

/**
// https://www.cssscript.com/star-rating-component-javascript-rater/
// https://github.com/fredolss/rater-js

/*
Rating star script methods
// disable
myRating.disable();

// enable
myRating.enable();

// set the rating value
myRating.setRating(rating:number);

// get the rating value
myRating.getRating();

// clear the rating
myRating.clear();

// removes event handlers
myRating.dispose();

// gets the element
myRating.element();

 *
 * @since  4.0.0
 */
class RatingField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected string $type = 'rating';

    protected string $layout = 'joomla.form.field.list-fancy-select';

    /**
     * The translate.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $translate = true;

    protected $header = false;

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
    protected function processQuery()
    {
        // Type of input the field shows
        $this->input_type = $this->getAttribute('input_type');

        // Database Table
        $this->table = $this->getAttribute('table');

        // The field that the field will save on the database
        $this->key_field = (string) $this->getAttribute('key_field');

        // The column that the field shows in the input
        $this->value_field = (string) $this->getAttribute('value_field');

        // The option field that the field will save on the database
        $this->option_key_field = (string) $this->getAttribute('option_key_field');

        // The option value that the field shows in the input
        $this->option_value_field = (string) $this->getAttribute('option_value_field');

        // Flag to identify if the fk_value is multiple
        $this->value_multiple = (int) $this->getAttribute('value_multiple', 0);

        $this->required = (string) $this->getAttribute('required', 0);

        // Flag to identify if the fk_value hides the trashed items
        $this->hideTrashed = (int) $this->getAttribute('hide_trashed', 0);

        // Flag to identify if the fk has default order
        $this->ordering = (int) $this->getAttribute('ordering', 0);

        // The where SQL for foreignkey
        $this->condition = (string) $this->getAttribute('condition');

        // Flag for translate options
        $this->translate = (bool) $this->getAttribute('translate');

        // Initialize variables.
        $html     = '';
        $fk_value = '';

        // Load all the field options
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Support for multiple fields on fk_values
        if ($this->value_multiple == 1) {
            // Get the fields for multiple value
            $this->value_fields = (string) $this->getAttribute('value_field_multiple');
            $this->value_fields = explode(',', $this->value_fields);
            $this->separator    = (string) $this->getAttribute('separator');

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

        if ($this->ordering) {
            $query->order('ordering ASC');
        }

        if ($this->condition) {
            $query->where($this->condition);
        }

        // Only join on data that the user has created
        $user = Factory::getUser();
        if (!empty($user->id)) {
            $query->where("created_by = " . (int)$user->id);
        }

        return $query;
    }

    /**
     * Method to get the field input for a foreignkey field.
     *
     * @return  string  The field input.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        if (!is_array($this->value) && !empty($this->value)) {
            if (is_object($this->value)) {
                $this->value = get_object_vars($this->value);
            }

            // String in format 2,5,4
            if (is_string($this->value)) {
                $this->value = explode(',', $this->value);
            }

            // Integer is given
            if (is_int($this->value)) {
                $this->value = [$this->value];
            }

            $data['value'] = $this->value;
        }

        $data['options']       = $this->getOptions();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   4.0.0
     * @throws Exception
     * @throws Exception
     */
    protected function getOptions()
    {
        $options = [];
        $db      = Factory::getDbo();
        try {
            $db->setQuery($this->processQuery());
            $results = $db->loadObjectList();
        } catch (ExecutionFailureException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        // Add header.
        if (!empty($this->header)) {
            $options[] = (object) ["value" => '', "text" => Text::_($this->header)];
        }

        if (!empty($this->option_value_field) || !empty($this->option_key_field)) {
            $options[] = (object) ["value" => $this->option_key_field, "text" => Text::_($this->option_value_field)];
        }

        // Build the field options.
        if (!empty($results)) {
            foreach ($results as $item) {
                $options[] = (object) [
                                    "value" => $item->{$this->key_field},
                                    "text"  => $this->translate == true ? Text::_($item->{$this->value_field}) : $item->{$this->value_field},
                                ];
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    /**
     * Wrapper method for getting attributes from the form element
     *
     * @param   string  $attr_name  Attribute name
     * @param   mixed   $default    Optional value to return if attribute not found
     *
     * @return mixed The value of the attribute if it exists, null otherwise
     */
    public function getAttribute($attr_name, $default = null)
    {
        if (!empty($this->element[$attr_name])) {
            return $this->element[$attr_name];
        } else {
            return $default;
        }
    }
}
