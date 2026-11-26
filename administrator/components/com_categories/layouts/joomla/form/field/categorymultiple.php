<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\CMS\Layout\FileLayout $this */

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes to apply to the input.
 * @var   boolean  $disabled        Is the field disabled?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   boolean  $readonly        Is the field read only?
 * @var   boolean  $required        Is the field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   mixed    $value           Value attribute of the field.
 * @var   array    $options         Options available for this field.
 * @var   boolean  $allowCustom     Whether custom options are allowed.
 * @var   string   $customPrefix    Optional prefix for new categories.
 * @var   boolean  $refreshPage     Whether the form must be refreshed after changing the field.
 * @var   string   $refreshCatId    Categories that trigger a refresh.
 * @var   string   $refreshSection  Form section to refresh.
 */

$html    = [];
$classes = [];
$attr    = '';
$attr2   = '';

// Initialize some field attributes.
$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= $multiple ? ' multiple' : '';
$attr .= $autofocus ? ' autofocus' : '';
$attr .= $onchange ? ' onchange="' . $onchange . '"' : '';

// To avoid user's confusion, readonly="true" should imply disabled="disabled".
if ($readonly || $disabled) {
    $attr .= ' disabled="disabled"';
}

$attr2 .= !empty($class) ? ' class="' . $class . '"' : '';

$placeholder = $this->escape(Text::_('JGLOBAL_TYPE_OR_SELECT_CATEGORY'));

$attr2 .= ' placeholder="' . $placeholder . '" ';
$attr2 .= ' search-placeholder="' . $placeholder . '" ';

if ($allowCustom) {
    $attr2 .= ' allow-custom';

    if ($customPrefix !== '') {
        $attr2 .= ' new-item-prefix="' . $customPrefix . '" ';
    }
}

if ($required) {
    $attr  .= ' required class="required"';
    $attr2 .= ' required';
}

if ($readonly) {
    $html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $value, $id);

    if ($multiple && is_array($value)) {
        if (!count($value)) {
            $value[] = '';
        }

        foreach ($value as $val) {
            $html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '">';
        }
    } else {
        $html[] = '<input type="hidden" id="' . $id . '-value" name="' . $name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '">';
    }
} else {
    // All Categories have been deleted, so we need a new category (This will create on save if selected).
    if (count($options) === 0) {
        $options[0]            = new \stdClass();
        $options[0]->value     = 'Uncategorised';
        $options[0]->text      = 'Uncategorised';
        $options[0]->level     = '1';
        $options[0]->published = '1';
        $options[0]->lft       = '1';
    }

    $html[] = HTMLHelper::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);
}

if ($refreshPage === true) {
    $attr2 .= ' data-refresh-catid="' . $refreshCatId . '" data-refresh-section="' . $refreshSection . '"';
} else {
    $attr2 .= $onchange ? ' onchange="' . $onchange . '"' : '';
}

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select');

?>
<joomla-field-fancy-select <?php echo $attr2; ?>><?php echo implode($html); ?></joomla-field-fancy-select>
