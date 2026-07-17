<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use InvalidArgumentException;
use Joomla\CMS\Language\Text;

/**
 * Renders a <select> list whose options are the cases of a PHP enum.
 *
 * Usage:
 *   <field name="ticket_type" type="enum" enum="Jed\Component\Tickets\Administrator\Enum\TicketType" />
 *
 * The option value is the case's backing value (for a backed enum) or its name (for a pure enum).
 * The option text is the case's name, or - if a "prefix" attribute is given - the translation of
 * "{PREFIX}_{CASE_NAME}" (uppercased).
 *
 * @since __DEPLOY_VERSION__
 */
class EnumField extends ListField
{
    /**
     * The enum field type.
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $type = 'enum';

    /**
     * Method to get the field options.
     *
     * @return array The field option objects.
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getOptions(): array
    {
        $enumClass = (string) $this->element['enum'];

        if ($enumClass === '' || !enum_exists($enumClass)) {
            throw new InvalidArgumentException(
                \sprintf('Field "%s" needs a valid "enum" attribute (got "%s").', $this->name, $enumClass)
            );
        }

        $prefix  = (string) $this->element['prefix'];
        $options = [];

        /** @var \UnitEnum $case */
        foreach ($enumClass::cases() as $case) {
            $options[] = (object) [
                'value' => $case instanceof \BackedEnum ? $case->value : $case->name,
                'text'  => $prefix !== '' ? Text::_(\strtoupper($prefix . '_' . $case->name)) : $case->name,
            ];
        }

        // Merge any additional options (e.g. a blank "please select") from the XML definition.
        return array_merge(parent::getOptions(), $options);
    }
}
