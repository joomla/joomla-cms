<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
 * @var   string   $userName        The name of the user.
 * @var   mixed    $groups          The filtering groups (null means no filtering).
 * @var   mixed    $exclude         The users to exclude from the list of users.
 * @var   mixed    $basetype        The base type to add to the path.
 * @var   mixed    $allowClear      Allows to clear form field input.
 * @var   mixed    $allowEdit       Allows to edit the active user in a modal.
 * @var   mixed    $allowNew        Allows to create a new user in a modal.
 */

// Set the link for the user selection page
$linkSelect = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component'
	. '&amp;required=' . ($required ? 1 : 0)
	. '&amp;function=jSelectUser_' . $id
	. '&amp;inputid=' . (string) $id
	. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
	. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '')
	. (isset($basetype) ? ('&amp;basetype=' . (string) $basetype) : '');

// Set the link for the user edition page
$linkEdit   = 'index.php?option=com_users&amp;view=user&amp;layout=modal&amp;tmpl=component'
	. '&amp;inputid=' . $id
	. '&amp;task=user.edit'
	. '&amp;id=' . (int) $value;

// Build the script.
$script = array();

// Select button script
$script[] = '	function jSelectUser_' . $id . '(id, name) {';
$script[] = '		document.getElementById("' . $id . '_id").value = id;';
$script[] = '		document.getElementById("' . $id . '").value = name;';

if ($allowEdit)
{
	$script[] = '		if (id == "' . $value . '") {';
	$script[] = '			jQuery("#' . $id . '_edit").removeClass("hidden");';
	$script[] = '		} else {';
	$script[] = '			jQuery("#' . $id . '_edit").addClass("hidden");';
	$script[] = '		}';
}

if ($allowClear)
{
	$script[] = '		jQuery("#' . $id . '_clear").removeClass("hidden");';
}

$script[] = '		jQuery("#userSelect' . $id . 'Modal").modal("hide");';

if ($required)
{
	$script[] = '		document.formvalidator.validate(document.getElementById("' . $id . '_id"));';
	$script[] = '		document.formvalidator.validate(document.getElementById("' . $id . '"));';
}

$script[] = '	}';

// Edit button script
$script[] = '	function jEditUser(inputid, btn) {';
$script[] = '		var id = jQuery("#userEdit" + inputid + "Modal iframe").contents().find("#jform_id").val();';
$script[] = '		var name = jQuery("#userEdit" + inputid + "Modal iframe").contents().find("#jform_name").val();';
$script[] = '		window.parent.jQuery("#" + inputid + "_id").val(id);';
$script[] = '		window.parent.jQuery("#" + inputid).val(name);';
$script[] = '		jQuery("#userEdit" + inputid + "Modal iframe").contents().find("#" + btn).click();';
$script[] = '		if (btn == "save") {';
$script[] = '			jQuery("#userEdit' . $id . 'Modal").modal("hide");';
$script[] = '		}';
$script[] = '	}';

// No User button script
static $scriptClear;

if ($allowClear && ! $scriptClear)
{
	$scriptClear = true;

	$script[] = '	function jClearUser(id) {';
	$script[] = '		document.getElementById(id + "_id").value = "";';
	$script[] = '		document.getElementById(id).value = "";';
	$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
	$script[] = '		jQuery("#"+id).attr("placeholder", "'
		. htmlspecialchars(JText::_('JLIB_FORM_CHANGE_USER', true), ENT_COMPAT, 'UTF-8') . '");';
	$script[] = '		if (document.getElementById(id + "_edit")) {';
	$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
	$script[] = '		}';
	$script[] = '		return false;';
	$script[] = '	}';
}

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<span class="input-append">
	<input
		class="<?php echo ($readonly ? '' : 'input-medium'); ?><?php echo $class ? ' ' . (string) $class : ''?>"
		id="<?php echo $id; ?>"
		type="text"
		value="<?php echo htmlspecialchars($userName, ENT_COMPAT, 'UTF-8'); ?>"
		placeholder="<?php echo JText::_('JLIB_FORM_CHANGE_USER'); ?>"
		readonly
		<?php echo $size ? ' size="' . (int) $size . '"' : ''; ?>
		<?php echo $required ? 'required' : ''; ?>
	/>

	<?php if ( ! $readonly) : ?>
		<?php // Select user button ?>
		<a
			class="btn btn-primary hasTooltip"
			data-toggle="modal"
			href="#userSelect<?php echo $id; ?>Modal"
			title="<?php echo JHtml::tooltipText('JLIB_FORM_CHANGE_USER'); ?>"
			role="button"
			>
			<span class="icon-user"></span>
			<span class="visible-desktop"><?php echo JText::_('JSELECT'); ?></span>
		</a>

		<?php // Edit user button ?>
		<?php if ($allowEdit || $allowNew) : ?>
			<a
				class="btn hasTooltip<?php echo (($allowEdit && $value || $allowNew) ? '' : ' hidden'); ?>"
				id="<?php echo $id; ?>_edit"
				data-toggle="modal"
				href="#userEdit<?php echo $id; ?>Modal"
				title="<?php echo JHtml::tooltipText($value ? 'JLIB_FORM_EDIT_USER' : 'JLIB_FORM_NEW_USER'); ?>"
				role="button"
				>
				<span class="icon-<?php echo ($value ? 'edit' : 'new'); ?>"></span>
				<span class="visible-desktop"><?php echo ($value ? JText::_('JLIB_FORM_EDIT') : JText::_('JLIB_FORM_NEW')); ?></span>
			</a>
		<?php endif; ?>

		<?php // No User button ?>
		<?php if ($allowClear) : ?>
			<button
				class="btn hasTooltip<?php echo ($value ? '' : ' hidden'); ?>"
				id="<?php echo $id; ?>_clear"
				onclick="return jClearUser('<?php echo $id; ?>')"
				>
				<span class="icon-remove"></span>
				<span class="visible-desktop"><?php echo JText::_('JLIB_FORM_NO_USER'); ?></span>
			</button>
		<?php endif; ?>

	<?php endif; ?>
</span>

<?php // Select user modal ?>
<?php if ( ! $readonly) : ?>
	<?php echo JHtml::_(
		'bootstrap.renderModal',
		'userSelect' . $id . 'Modal',
		array(
			'title'       => JText::_('JLIB_FORM_CHANGE_USER'),
			'url'         => $linkSelect,
			'height'      => '400px',
			'width'       => '800px',
			'bodyHeight'  => '70',
			'modalWidth'  => '80',
			'footer'      => '<button class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>',
		)
	); ?>
<?php endif; ?>

<?php // Edit user modal ?>
<?php if ($allowEdit) : ?>
	<?php echo JHtml::_(
		'bootstrap.renderModal',
		'userEdit' . $id . 'Modal',
		array(
			'title'       => JText::_('JLIB_FORM_EDIT_USER'),
			'backdrop'    => 'static',
			'keyboard'    => false,
			'closeButton' => false,
			'url'         => $linkEdit,
			'height'      => '400px',
			'width'       => '400px',
			'bodyHeight'  => '70',
			'modalWidth'  => '60',
			'footer'      => '<button class="btn" data-dismiss="modal" type="button"'
					. ' onclick="jQuery(\'#userEdit' . $id . 'Modal iframe\').contents().find(\'#closeBtn\').click();" aria-hidden="true">'
					. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
					. '<button class="btn btn-primary" type="button" onclick="jEditUser(\'' . $id . '\', \'saveBtn\')" aria-hidden="true">'
					. JText::_("JSAVE") . '</button>'
					. '<button class="btn btn-success" type="button" onclick="jEditUser(\'' . $id . '\', \'applyBtn\')" aria-hidden="true">'
					. JText::_("JAPPLY") . '</button>',
		)
	); ?>
<?php endif; ?>

<?php // Create the real field, hidden, that stored the user id. ?>
<input id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" type="hidden" value="<?php echo (int) $value; ?>" />
