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
        $this->canDo['select'] = ((string) $this->element['select'] != 'false');
        $this->canDo['new']    = ((string) $this->element['new'] == 'true');
        $this->canDo['edit']   = ((string) $this->element['edit'] == 'true');
        $this->canDo['clear']  = ((string) $this->element['clear'] != 'false');

        // Prepare Urls
        $this->urls['select']  = (string) $this->element['urlSelect'];
        $this->urls['new']     = (string) $this->element['urlNew'];
        $this->urls['edit']    = (string) $this->element['urlEdit'];
        $this->urls['checkin'] = (string) $this->element['urlCheckin'];

        // Prepare titles
        $this->modalTitles['select']  = (string) $this->element['titleSelect'];
        $this->modalTitles['new']     = (string) $this->element['titleNew'];
        $this->modalTitles['edit']    = (string) $this->element['titleEdit'];

        return $result;
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
