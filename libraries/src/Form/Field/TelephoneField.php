<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#telephone-state-(type=tel)
 * @see    \Joomla\CMS\Form\Rule\TelRule for telephone number validation
 * @see    JHtmlTel for rendering of telephone numbers
 * @since  1.7.0
 */
class TelephoneField extends TextField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Telephone';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7.0
     */
    protected $layout = 'joomla.form.field.tel';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        // Trim the trailing line in the layout file
        return rtrim($this->getRenderer($this->layout)->render($this->collectLayoutData()), PHP_EOL);
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.7.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Initialize some field attributes.
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';

        $extraData = [
            'maxLength' => $maxLength,
        ];

        return array_merge($data, $extraData);
    }
}
