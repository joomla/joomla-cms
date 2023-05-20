<?php

/**
 * @package       JED
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Field;

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
class TimecreatedField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'timecreated';

    /**
     * Method to get the field input markup.
     *
     * @return  string    The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput(): string
    {
        // Initialize variables.
        $html = [];

        $time_created = $this->value;

        if (!strtotime($time_created)) {
            $time_created = Factory::getDate('now', Factory::getConfig()->get('offset'))->toSql(true);
            $html[]       = '<input type="hidden" name="' . $this->name . '" value="' . $time_created . '" />';
        }

        $hidden = (bool) $this->element['hidden'];

        if ($hidden == null || !$hidden) {
            $jdate       = new Date($time_created);
            $pretty_date = $jdate->format(Text::_('DATE_FORMAT_LC2'));
            $html[]      = "<div>" . $pretty_date . "</div>";
        }

        return implode($html);
    }
}
