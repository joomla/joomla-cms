<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#text-(type=text)-state-and-search-state-(type=search)
 * @since  1.7.0
 */
class NoteField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Note';

    /**
     * Hide the label when rendering the form field.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $hiddenLabel = true;

    /**
     * Hide the description when rendering the form field.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $hiddenDescription = true;

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   1.7.0
     */
    protected function getLabel()
    {
        if (empty($this->element['label']) && empty($this->element['description'])) {
            return '';
        }

        $html  = [];
        $class = [];

        if (!empty($this->class)) {
            $class[] = $this->class;
        }

        if ($close = (string) $this->element['close']) {
            HTMLHelper::_('bootstrap.alert');
            $close   = $close === 'true' ? 'alert' : $close;
            $html[]  = '<button type="button" class="btn-close" data-bs-dismiss="' . $close . '"></button>';
            $class[] = 'alert-dismissible show';
        }

        $class       = $class ? ' class="' . implode(' ', $class) . '"' : '';
        $title       = $this->element['label'] ? (string) $this->element['label'] : ($this->element['title'] ? (string) $this->element['title'] : '');
        $heading     = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
        $description = (string) $this->element['description'];
        $html[]      = !empty($title) ? '<' . $heading . '>' . Text::_($title) . '</' . $heading . '>' : '';
        $html[]      = !empty($description) ? Text::_($description) : '';

        return '</div><div ' . $class . '>' . implode('', $html);
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        return '';
    }
}
