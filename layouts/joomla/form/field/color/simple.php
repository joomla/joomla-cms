<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

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
 * @var   boolean  $spellchec       Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $checked         Is this field checked?
 * @var   array    $position        Position of input.
 * @var   array    $control         The forms control.
 * @var   array    $colors          The specified colors
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 */
Factory::getDocument()->getWebAssetManager()
    ->useStyle('webcomponent.field-simple-color')
    ->useScript('webcomponent.field-simple-color');

Text::script('JCLOSE');
Text::script('JNONE');

$slots = [];
$attr  = [
    'name'  => $name,
    'id'    => $id,
    'class' => '' . trim($class),
    'value' => trim($color),
];

if ($disabled) {
    $attr['disabled'] = '';
}

if ($readonly) {
    $attr['readonly'] = '';
}

foreach ($colors as $key => $val) {
    $slots[] = '<button slot="colors" value="' . trim($val) . '" aria-pressed="' . (trim($val) === $color ? 'true' : 'false') . '" type="button"></button>';
}
?>
<joomla-field-simple-color <?php echo ArrayHelper::toString($attr); ?>>
    <?php echo implode('', $slots); ?>
    <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $color; ?>" />
</joomla-field-simple-color>
