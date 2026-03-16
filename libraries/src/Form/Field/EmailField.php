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
 * Provides and input field for email addresses
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#email-state-(type=email)
 * @see    \Joomla\CMS\Form\Rule\EmailRule
 * @since  1.7.0
 */
class EmailField extends TextField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Email';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.email';

    /**
     * Method to get the field input markup for email addresses.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
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
     * @since 3.5
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $extraData = [
            'maxLength' => $this->maxLength,
            'multiple'  => $this->multiple,
        ];

        return array_merge($data, $extraData);
    }
}
