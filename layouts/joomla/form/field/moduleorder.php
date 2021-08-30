<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
 * @var   array    $spellcheck      Options available for this field.
 * @var   string   $accept          File types that are accepted.
 */

$attr  = '';

// Initialize some field attributes.
$attr .= !empty($class) ? ' class="module-ajax-ordering ' . $class . '"' : 'class="module-ajax-ordering"';
$attr .= $disabled ? ' disabled' : '';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';

// Initialize JavaScript field attributes.
$attr .= !empty($onchange) ? ' onchange="' . $onchange . '"' : '';

// Including fallback code for HTML5 non supported browsers.
JHtml::_('behavior.core');
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0));
JHtml::_('script', 'system/moduleorder.js', array('version' => 'auto', 'relative' => true));
?>
<div id="parent_<?php echo $id; ?>" <?php echo $attr; ?> data-url="<?php echo 'index.php?option=com_modules&task=module.orderPosition&'
. $token; ?>" data-element="<?php echo 'parent_' . $id; ?>" data-ordering="<?php echo $ordering; ?>" data-position-element="<?php
echo $element; ?>" data-client-id="<?php echo $clientId; ?>" data-module-id="<?php echo $moduleId; ?>" data-name="<?php echo $name; ?>"></div>
