<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
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
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   string   $userName        The user name
 * @var   mixed    $groups          The filtering groups (null means no filtering)
 * @var   mixed    $excluded        The users to exclude from the list of users
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 */

$uri = new Uri('index.php?option=com_users&view=users&layout=modal&tmpl=component');

$uri->setVar('field', $this->escape($id));
$uri->setVar('required', $required ? 1 : 0);

if (!empty($groups)) {
    $uri->setVar('groups', base64_encode(json_encode($groups)));
}

if (!empty($excluded)) {
    $uri->setVar('excluded', base64_encode(json_encode($excluded)));
}

$attr = [
    'class'    => (string) $class,
    'id'       => (string) $id,
    'name'     => (string) $name,
    'value'    => $this->escape($value),
    'username' => $this->escape($userName),
    'url'      => (string) $uri
];

if ($required) {
    $attr['required'] = '';
}

if ($readonly) {
    $attr['readonly'] = '';
}

Text::script('JLIB_FORM_CHANGE_USER');

Factory::getApplication()->getDocument()->getWebAssetManager()
    ->useStyle('webcomponent.field-user')
    ->useScript('webcomponent.field-user');
?>
<joomla-field-user <?php echo ArrayHelper::toString($attr); ?>>
    <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
</joomla-field-user>
