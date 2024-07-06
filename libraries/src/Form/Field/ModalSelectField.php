<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\Database\ParameterType;

/**
 * Provides a modal content selection
 *
 * @since  5.0.0
 */
class ModalSelectField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  5.0.0
     */
    protected $type = 'ModalSelect';

    /**
     * Layout to render
     *
     * @var    string
     * @since  5.0.0
     */
    protected $layout = 'joomla.form.field.modal-select';

    /**
     * Enabled actions: select, clear, edit, new
     *
     * @var    boolean[]
     * @since  5.0.0
     */
    protected $canDo = [];

    /**
     * Urls for modal: select, edit, new
     *
     * @var    string[]
     * @since  5.0.0
     */
    protected $urls = [];

    /**
     * List of titles for each modal type: select, edit, new
     *
     * @var    string[]
     * @since  5.0.0
     */
    protected $modalTitles = [];

    /**
     * List of icons for each button type: select, edit, new
     *
     * @var    string[]
     * @since  5.0.0
     */
    protected $buttonIcons = [];

    /**
     * The table name to select the title related to the field value.
     *
     * @var     string
     * @since   __DEPLOY_VERSION__
     */
    protected $sql_title_table = '';

    /**
     * The column name in the $sql_title_table, to select the title related to the field value.
     *
     * @var     string
     * @since   __DEPLOY_VERSION__
     */
    protected $sql_title_column = '';

    /**
     * The key name in the $sql_title_table that represent the field value, to select the title related to the field value.
     *
     * @var     string
     * @since   __DEPLOY_VERSION__
     */
    protected $sql_title_key = '';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   5.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        // Prepare enabled actions
        $this->__set('select', (string) $this->element['select'] != 'false');
        $this->__set('new', (string) $this->element['new'] == 'true');
        $this->__set('edit', (string) $this->element['edit'] == 'true');
        $this->__set('clear', (string) $this->element['clear'] != 'false');

        // Prepare Urls and titles
        foreach (
            ['urlSelect', 'urlNew', 'urlEdit', 'urlCheckin', 'titleSelect', 'titleNew', 'titleEdit', 'iconSelect',
                     'sql_title_table', 'sql_title_column', 'sql_title_key',] as $attr
        ) {
            $this->__set($attr, (string) $this->element[$attr]);
        }

        return $result;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   5.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'select':
                return $this->canDo['select'] ?? true;
            case 'new':
                return $this->canDo['new'] ?? false;
            case 'edit':
                return $this->canDo['edit'] ?? false;
            case 'clear':
                return $this->canDo['clear'] ?? true;
            case 'urlSelect':
                return $this->urls['select'] ?? '';
            case 'urlNew':
                return $this->urls['new'] ?? '';
            case 'urlEdit':
                return $this->urls['edit'] ?? '';
            case 'urlCheckin':
                return $this->urls['checkin'] ?? '';
            case 'titleSelect':
                return $this->modalTitles['select'] ?? '';
            case 'titleNew':
                return $this->modalTitles['new'] ?? '';
            case 'titleEdit':
                return $this->modalTitles['edit'] ?? '';
            case 'iconSelect':
                return $this->buttonIcons['select'] ?? '';
            case 'sql_title_table':
            case 'sql_title_column':
            case 'sql_title_key':
                return $this->$name;
            default:
                return parent::__get($name);
        }
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'select':
                $this->canDo['select'] = (bool) $value;
                break;
            case 'new':
                $this->canDo['new'] = (bool) $value;
                break;
            case 'edit':
                $this->canDo['edit'] = (bool) $value;
                break;
            case 'clear':
                $this->canDo['clear'] = (bool) $value;
                break;
            case 'urlSelect':
                $this->urls['select'] = (string) $value;
                break;
            case 'urlNew':
                $this->urls['new'] = (string) $value;
                break;
            case 'urlEdit':
                $this->urls['edit'] = (string) $value;
                break;
            case 'urlCheckin':
                $this->urls['checkin'] = (string) $value;
                break;
            case 'titleSelect':
                $this->modalTitles['select'] = (string) $value;
                break;
            case 'titleNew':
                $this->modalTitles['new'] = (string) $value;
                break;
            case 'titleEdit':
                $this->modalTitles['edit'] = (string) $value;
                break;
            case 'iconSelect':
                $this->buttonIcons['select'] = (string) $value;
                break;
            case 'sql_title_table':
            case 'sql_title_column':
            case 'sql_title_key':
                $this->$name = (string) $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   5.0.0
     */
    protected function getInput()
    {
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        // Get the layout data
        $data = $this->collectLayoutData();

        // Load the content title here to avoid a double DB Query
        $data['valueTitle'] = $this->getValueTitle();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   5.0.0
     */
    protected function getValueTitle()
    {
        // Selecting the title for the field value, when required info were given
        if ($this->value && $this->sql_title_table && $this->sql_title_column && $this->sql_title_key) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName($this->sql_title_column))
                    ->from($db->quoteName($this->sql_title_table))
                    ->where($db->quoteName($this->sql_title_key) . ' = :value')
                    ->bind(':value', $this->value, ParameterType::INTEGER);
                $db->setQuery($query);

                return $db->loadResult() ?: $this->value;
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $this->value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 5.0.0
     */
    protected function getLayoutData()
    {
        $data                = parent::getLayoutData();
        $data['canDo']       = $this->canDo;
        $data['urls']        = $this->urls;
        $data['modalTitles'] = $this->modalTitles;
        $data['buttonIcons'] = $this->buttonIcons;

        return $data;
    }
}
