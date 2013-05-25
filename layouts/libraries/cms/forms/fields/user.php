<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

$html = array();

$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $data->id
	. (isset($data->groups) ? ('&amp;groups=' . base64_encode(json_encode($data->groups))) : '')
	. (isset($data->excluded) ? ('&amp;excluded=' . base64_encode(json_encode($data->excluded))) : '');

// Initialize some field attributes.
$attr = $data->element['class'] ? ' class="' . (string) $data->element['class'] . '"' : '';
$attr .= $data->element['size'] ? ' size="' . (int) $data->element['size'] . '"' : '';

// Initialize JavaScript field attributes.
$onchange = (string) $data->element['onchange'];

// Load the modal behavior script.
JHtml::_('behavior.modal', 'a.modal_' . $data->id);

// Build the script.
$script = "
	function jSelectUser_" . $data->id . "(id, title) {
		var old_id = document.getElementById('" . $data->id . "_id').value;
		if (old_id != id) {
			document.getElementById('" . $data->id . "_id').value = id;
			document.getElementById('" . $data->id . "_name').value = title;
			" . $onchange . "
		}
		SqueezeBox.close();
	}
";

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration($script);

// Load the current username if available.
$table = JTable::getInstance('user');

if ($data->value)
{
	$table->load($data->value);
}
else
{
	$table->username = JText::_('JLIB_FORM_SELECT_USER');
}
?>
<?php // Create a dummy text field with the user name. ?>
<div class="input-append">
	<input class="input-medium" type="text" id="<?php echo $data->id; ?>_name" value="<?php echo  htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8'); ?>" disabled="disabled" <?php echo $attr; ?> />
	<?php
	// Create the user select button.
	if ($data->element['readonly'] != 'true') : ?>
		<a class="btn btn-primary modal_<?php echo $data->id; ?>" title="<?php echo JText::_('JLIB_FORM_CHANGE_USER'); ?>" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
			<i class="icon-user"></i>
		</a>
	<?php endif; ?>
</div>

<?php // Create the real field, hidden, that stored the user id. ?>
<input type="hidden" id="<?php echo $data->id; ?>_id" name="<?php echo $data->name; ?>" value="<?php echo (int) $data->value; ?>" />
