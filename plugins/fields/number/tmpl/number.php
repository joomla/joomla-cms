<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Number
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$value    = $field->value;
$min      = $field->fieldparams->get('min', null);
$currency = $field->fieldparams->get('currency', 0);
$decimals = $field->fieldparams->get('decimals', 2);
$symbol   = $field->fieldparams->get('symbol', 2);
$position = $field->fieldparams->get('position', 2);

if (is_numeric($value)) {
    $value = (float)$value;
    if ($currency) {
        $formattedCurrency = number_format($value, $decimals, Text::_('DECIMALS_SEPARATOR'), Text::_('THOUSANDS_SEPARATOR'));
        $value = $position ? ($formattedCurrency . $symbol) : ($symbol . $formattedCurrency) ;
    }
} else {
    $value = $min ?? '';
}

echo $value;
