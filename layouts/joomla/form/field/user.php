<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
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
 *
 * @var   string   $userName        The user name
 * @var   mixed    $groups          The filtering groups (null means no filtering)
 * @var   mixed    $excluded        The users to exclude from the list of users
 */

if (!$readonly)
{
	// @TODO remove jQuery dependency once modal moves to webcomponents
	HTMLHelper::_('jquery.framework');

	HTMLHelper::_('webcomponent', ['joomla-field-user' =>'system/webcomponents/joomla-field-user.min.js'], ['version' => 'auto', 'relative' => true]);}

$uri = new Uri('index.php?option=com_users&view=users&layout=modal&tmpl=component&required=0');

$uri->setVar('field', $this->escape($id));

if ($required)
{
	$uri->setVar('required', 1);
}

if (!empty($groups))
{
	$uri->setVar('groups', base64_encode(json_encode($groups)));
}

if (!empty($excluded))
{
	$uri->setVar('excluded', base64_encode(json_encode($excluded)));
}

// Invalidate the input value if no user selected
if ($this->escape($userName) === Text::_('JLIB_FORM_SELECT_USER'))
{
	$userName = '';
}

$inputAttributes = array(
	'type' => 'text', 'id' => $id, 'class' => 'form-control field-user-input-name', 'value' => $this->escape($userName)
);
if ($class)
{
	$inputAttributes['class'] .= ' ' . $class;
}
if ($size)
{
	$inputAttributes['size'] = (int) $size;
}
if ($required)
{
	$inputAttributes['required'] = 'required';
}
if (!$readonly)
{
	$inputAttributes['placeholder'] = Text::_('JLIB_FORM_SELECT_USER');
}
?>
<?php // Create a dummy text field with the user name. ?>
<joomla-field-user class="field-user-wrapper"
		url="<?php echo (string) $uri; ?>"
		modal=".modal"
		modal-width="100%"
		modal-height="400px"
		input=".field-user-input"
		input-name=".field-user-input-name"
		button-select=".button-select">
	<div class="input-group">
		<input <?php echo ArrayHelper::toString($inputAttributes); ?> readonly>
		<?php if (!$readonly) : ?>
			<span class="input-group-append">
					<a class="btn btn-primary button-select" title="<?php echo Text::_('JLIB_FORM_CHANGE_USER') ?>"><span class="fa fa-user icon-white" aria-hidden="true"></span></a>
				<?php echo HTMLHelper::_(
					'bootstrap.renderModal',
					'userModal_' . $id,
					array(
						'url'         => $uri,
						'title'       => Text::_('JLIB_FORM_CHANGE_USER'),
						'closeButton' => true,
						'height'      => '100%',
						'width'       => '100%',
						'modalWidth'  => 80,
						'bodyHeight'  => 60,
						'footer'      => '<a type="button" class="btn btn-secondary" data-dismiss="modal">' . Text::_('JCANCEL') . '</a>'
					)
				); ?>
				</span>
		<?php endif; ?>
	</div>
	<?php // Create the real field, hidden, that stored the user id. ?>
	<?php if (!$readonly) : ?>
		<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo (int) $value; ?>"
				class="field-user-input <?php echo $class ? (string) $class : ''?>"
				data-onchange="<?php echo $this->escape($onchange); ?>">
	<?php endif; ?>
</joomla-field-user>
