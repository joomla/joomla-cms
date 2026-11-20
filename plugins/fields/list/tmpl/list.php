<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.List
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Plugin\Fields\ListField\Extension\ListPlugin $this */
$fieldValue = $field->value;

if ($fieldValue == '') {
    return;
}

$fieldValue = (array) $fieldValue;
$texts      = [];
$options    = $this->getOptionsFromField($field);

foreach ($options as $value => $name) {
    if (in_array((string) $value, $fieldValue)) {
        $texts[] = Text::_($name);
    }
}

echo htmlentities(implode(', ', $texts));
