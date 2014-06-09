<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 * 	- id       : (string) DOM id of the element
 * 	- element  : (SimpleXMLElement) The object of the <field /> XML element that describes the form field.
 * 	- field    : (JFormField) Object to access to the field properties
 * 	- name     : (string) Name of the field to display
 * 	- required : (boolean) Is this field required?
 * 	- value    : (mixed) Value of the field
 * 	- class    : (string) CSS class to apply
 * 	- size     : (integer) Size for the input element
 * 	- groups   : (mixed) filtering groups (null means no filtering)
 * 	- exclude  : (mixed) users to exclude from the list of users
 *
 */

$html = array();

$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $id
	. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
	. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

// Initialize some field attributes.
$attr = $class ? ' class="' . (string) $class . '"' : '';
$attr .= $size ? ' size="' . (int) $size . '"' : '';

// Initialize JavaScript field attributes.
$onchange = (string) $element['onchange'];

// Load the modal behavior script.
JHtml::_('behavior.modal', 'a.modal_' . $id);

// Build the script.
$script = "
	function jSelectUser_" . $id . "(id, title) {
		var old_id = document.getElementById('" . $id . "_id').value;
		if (old_id != id) {
			document.getElementById('" . $id . "_id').value = id;
			document.getElementById('" . $id . "_name').value = title;
			" . $onchange . "
		}
		SqueezeBox.close();
	}
";

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration($script);

// Load the current username if available.
$table = JTable::getInstance('user');

if (is_numeric($value))
{
	$table->load($value);
}
// Handle the special case for "current".
elseif (strtoupper($value) == 'CURRENT')
{
	$table->load(JFactory::getUser()->id);
}
else
{
	$table->name = JText::_('JLIB_FORM_SELECT_USER');
}
?>
<?php // Create a dummy text field with the user name. ?>
<div class="input-append">
	<input
		type="text" id="<?php echo $id; ?>_name"
		value="<?php echo  htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8'); ?>"
		readonly
		disabled="disabled" <?php echo $attr; ?> />
	<?php
	// Create the user select button.
	if ($field->readonly === false) : ?>
		<a class="btn btn-primary modal_<?php echo $id; ?>" title="<?php echo JText::_('JLIB_FORM_CHANGE_USER'); ?>" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
			<i class="icon-user"></i>
		</a>
	<?php endif; ?>
</div>

<?php // Create the real field, hidden, that stored the user id. ?>
<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo (int) $value; ?>" />
