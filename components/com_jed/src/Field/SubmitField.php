<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Field;

// Check to ensure this file is included in Joomla!
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;

/**
 * Class SubmitField
 *
 * @since  4.0.0
 */
class SubmitField extends FormField
{
    protected $type = 'submit';

    protected $value;

    protected $for;

    /**
     * Get a form field markup for the input
     *
     * @return string
     */
    public function getInput()
    {
        $this->value = $this->getAttribute('value');

        return '<button id="' . $this->id . '"'
        . ' name="submit_' . $this->for . '"'
        . ' value="' . $this->value . '"'
        . ' title="' . Text::_('JSEARCH_FILTER_SUBMIT') . '"'
        . ' class="btn" style="margin-top: -10px;">'
        . Text::_('JSEARCH_FILTER_SUBMIT')
        . ' </button>';
    }
}
