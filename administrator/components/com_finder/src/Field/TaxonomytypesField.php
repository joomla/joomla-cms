<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Field;

use Joomla\CMS\Form\Field\CheckboxesField;

/**
 * Taxonomy Types field for the Finder package.
 * This is a helper to allow to save an empty set of
 * options by having a hidden field with a "none" value.
 *
 * @since  5.0.0
 */
class TaxonomytypesField extends CheckboxesField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  5.0.0
     */
    protected $type = 'TaxonomyTypes';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   5.0.0
     */
    protected function getInput()
    {
        $html = parent::getInput();

        $data = $this->getLayoutData();
        $data['id'] .= '_hidden';
        $data['value'] = 'none';

        return $html . $this->getRenderer('joomla.form.field.hidden')->render($data);
    }
}
