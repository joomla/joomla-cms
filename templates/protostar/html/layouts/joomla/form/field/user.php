<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
 *
 * @var   string   $userName        The user name
 * @var   mixed    $groups          The filtering groups (null means no filtering)
 * @var   mixed    $exclude         The users to exclude from the list of users
 */

// Set the link for the user selection page
$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required='
	. ($required ? 1 : 0) . '&amp;field={field-user-id}&amp;ismoo=0'
	. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
	. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

// Invalidate the input value if no user selected
if (JText::_('JLIB_FORM_SELECT_USER') === htmlspecialchars($userName, ENT_COMPAT, 'UTF-8'))
{
	$userName = '';
}

JHtml::_('script', 'jui/fielduser.min.js', array('version' => 'auto', 'relative' => true));
?>
<?php // Create a dummy text field with the user name. ?>
<div class="field-user-wrapper"
	data-url="<?php echo $link; ?>"
	data-modal=".modal"
	data-modal-width="100%"
	data-modal-height="400px"
	data-input=".field-user-input"
	data-input-name=".field-user-input-name"
	data-button-select=".button-select"
	>
	<div class="input-append">
		<input
			type="text" id="<?php echo $id; ?>"
			value="<?php echo  htmlspecialchars($userName, ENT_COMPAT, 'UTF-8'); ?>"
			placeholder="<?php echo JText::_('JLIB_FORM_SELECT_USER'); ?>"
			readonly
			class="field-user-input-name <?php echo $class ? (string) $class : ''?>"
			<?php echo $size ? ' size="' . (int) $size . '"' : ''; ?>
			<?php echo $required ? 'required' : ''; ?>/>
		<?php if (!$readonly) : ?>
			<a class="btn btn-primary button-select" title="<?php echo JText::_('JLIB_FORM_CHANGE_USER') ?>"><span class="icon-user"></span></a>
			<?php echo JHtml::_(
				'bootstrap.renderModal',
				'userModal_' . $id,
				array(
					'title'  => JText::_('JLIB_FORM_CHANGE_USER'),
					'closeButton' => true,
					'footer' => '<a type="button" class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</a>'
				)
			); ?>
		<?php endif; ?>
	</div>
	<?php // Create the real field, hidden, that stored the user id. ?>
	<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo (int) $value; ?>"
		class="field-user-input <?php echo $class ? (string) $class : ''?>"
		data-onchange="<?php echo $this->escape($onchange); ?>"/>
</div>
