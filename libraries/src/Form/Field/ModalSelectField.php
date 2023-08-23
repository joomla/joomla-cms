<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormField;

/**
 * Provides a modal content selection
 *
 * @since  __DEPLOY_VERSION__
 */
class ModalSelectField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'ModalSelect';

    /**
     * Layout to render
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.modal-select';

    /**
     * Enabled actions: select, clear, edit, new
     *
     * @var    boolean[]
     * @since  __DEPLOY_VERSION__
     */
    protected $canDo = [];

    /**
     * Urls to for modal: select, edit, new
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    protected $urls = [];

    /**
     * List of titles for each modal type: select, edit, new
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    protected $modalTitles = [];

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
     * @since   __DEPLOY_VERSION__
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
        foreach (['urlSelect', 'urlNew', 'urlEdit', 'urlCheckin', 'titleSelect', 'titleNew', 'titleEdit'] as $attr) {
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        // Get the layout data
        $data = $this->getLayoutData();

        // Load the content title here to avoid a double DB Query
        $data['valueTitle'] = $this->getValueTitle();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getValueTitle()
    {
        return $this->value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data                = parent::getLayoutData();
        $data['canDo']       = $this->canDo;
        $data['urls']        = $this->urls;
        $data['modalTitles'] = $this->modalTitles;

        return $data;
    }
}
