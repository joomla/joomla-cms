<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multi-select form field, supporting inputs of:
 * minutes, hours, days of week, days of month and months.
 *
 * @since  4.1.0
 */
class CronField extends ListField
{
    /**
     * The subtypes supported by this field type.
     *
     * @var string[]
     *
     * @since  4.1.0
     */
    private const SUBTYPES = [
        'minutes',
        'hours',
        'days_month',
        'months',
        'days_week',
    ];

    /**
     * Count of predefined options for each subtype
     *
     * @var int[][]
     *
     * @since  4.1.0
     */
    private const OPTIONS_RANGE = [
        'minutes'    => [0, 59],
        'hours'      => [0, 23],
        'days_week'  => [1, 7],
        'days_month' => [1, 31],
        'months'     => [1, 12],
    ];

    /**
     * Response labels for the 'month' and 'days_week' subtypes.
     * The labels are language constants translated when needed.
     *
     * @var string[][]
     * @since  4.1.0
     */
    private const PREPARED_RESPONSE_LABELS = [
        'months' => [
            'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
            'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
        ],
        'days_week' => [
            'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY',
            'FRIDAY', 'SATURDAY', 'SUNDAY',
        ],
    ];

    /**
     * The form field type.
     *
     * @var    string
     *
     * @since  4.1.0
     */
    protected $type = 'cronIntervals';

    /**
     * The subtype of the CronIntervals field
     *
     * @var string
     * @since  4.1.0
     */
    private $subtype;

    /**
     * If true, field options will include a wildcard
     *
     * @var boolean
     * @since  4.1.0
     */
    private $wildcard;

    /**
     * If true, field will only have numeric labels (for days_week and months)
     *
     * @var boolean
     * @since  4.1.0
     */
    private $onlyNumericLabels;

    /**
     * Override the parent method to set deal with subtypes.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for
     *                                       the field. For example if the field has `name="foo"` and the group value is
     *                                       set to "bar" then the full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   4.1.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $parentResult = parent::setup($element, $value, $group);

        $subtype           = ((string) $element['subtype'] ?? '') ?: null;
        $wildcard          = ((string) $element['wildcard'] ?? '') === 'true';
        $onlyNumericLabels = ((string) $element['onlyNumericLabels']) === 'true';

        if (!($subtype && \in_array($subtype, self::SUBTYPES))) {
            return false;
        }

        $this->subtype           = $subtype;
        $this->wildcard          = $wildcard;
        $this->onlyNumericLabels = $onlyNumericLabels;

        return $parentResult;
    }

    /**
     * Method to get field options
     *
     * @return   array  Array of objects representing options in the options list
     *
     * @since  4.1.0
     */
    protected function getOptions(): array
    {
        $subtype = $this->subtype;
        $options = parent::getOptions();

        if (!\in_array($subtype, self::SUBTYPES)) {
            return $options;
        }

        if ($this->wildcard) {
            try {
                $options[] = HTMLHelper::_('select.option', '*', '*');
            } catch (\InvalidArgumentException) {
            }
        }

        [$optionLower, $optionUpper] = self::OPTIONS_RANGE[$subtype];

        // If we need text labels, we translate them first
        if (\array_key_exists($subtype, self::PREPARED_RESPONSE_LABELS) && !$this->onlyNumericLabels) {
            $labels = array_map(
                static function (string $string): string {
                    return Text::_($string);
                },
                self::PREPARED_RESPONSE_LABELS[$subtype]
            );
        } else {
            $labels = range(...self::OPTIONS_RANGE[$subtype]);
        }

        for ([$i, $l] = [$optionLower, 0]; $i <= $optionUpper; $i++, $l++) {
            try {
                $options[] = HTMLHelper::_('select.option', (string) ($i), $labels[$l]);
            } catch (\InvalidArgumentException) {
            }
        }

        return $options;
    }
}
