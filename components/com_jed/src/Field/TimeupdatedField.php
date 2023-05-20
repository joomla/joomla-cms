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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Supports an HTML select list of categories
 *
 * @since  4.0.0
 */
class TimeupdatedField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'timeupdated';

    /**
     * Method to get the field input markup.
     *
     * @return  string    The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        // Initialize variables.
        $html = [];

        $old_time_updated = $this->value;
        $hidden           = (bool) $this->element['hidden'];

        if ($hidden == null || !$hidden) {
            if (!strtotime($old_time_updated)) {
                $html[] = '-';
            } else {
                $jdate       = new Date($old_time_updated);
                $pretty_date = $jdate->format(Text::_('DATE_FORMAT_LC2'));
                $html[]      = "<div>" . $pretty_date . "</div>";
            }
        }

        $time_updated = Factory::getDate()->toSql();
        $html[]       = '<input type="hidden" name="' . $this->name . '" value="' . $time_updated . '" />';

        return implode($html);
    }
}
