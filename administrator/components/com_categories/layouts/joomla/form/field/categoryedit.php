<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 * @var   string   $customPrefix    Optional prefix for new categories.
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

// Create a read-only list (no name) with hidden input(s) to store the value(s).
if ($readonly) {
    $html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $value, $id);

    // E.g. form field type tag sends $this->value as array
    if ($multiple && is_array($value)) {
        if (!count($value)) {
            $value[] = '';
        }

        foreach ($value as $val) {
            $html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '">';
        }
    } else {
        $html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '">';
    }
} else {
    // Create a regular list.
    if (count($options) === 0) {
        // All Categories have been deleted, so we need a new category (This will create on save if selected).
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
    $attr2 .= ' onchange="Joomla.categoryHasChanged(this)"';

    Factory::getDocument()->getWebAssetManager()
        ->registerAndUseScript('field.category-change', 'layouts/joomla/form/field/category-change.min.js', [], ['defer' => true], ['core'])
        ->useScript('webcomponent.core-loader');

    // Pass the element id to the javascript
    Factory::getDocument()->addScriptOptions('category-change', $id);
} else {
    $attr2 .= $onchange ? ' onchange="' . $onchange . '"' : '';
}

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

Factory::getDocument()->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select');
?>

<joomla-field-fancy-select <?php echo $attr2; ?>><?php echo implode($html); ?></joomla-field-fancy-select>
