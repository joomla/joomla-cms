<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ------------------
 *
 * @var string           $id The DOM id of the element
 * @var string           $name The name of the field
 * @var boolean          $required The required attribute
 * @var mixed            $value The value of the field (user id)
 * @var string           $class The CSS class to apply
 * @var integer          $size The ize for the input element
 * @var mixed            $groups The filtering groups (null means no filtering)
 * @var mixed            $exclude The users to exclude from the list of users
 * @var string           $onchange The script for on change event
 * @var string           $userName The user name
 * @var boolean          $readOnly Check for field read only attribute
 */

$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=' . $id
	. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
	. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

// Load the modal behavior script.
JHtml::_('behavior.modal', 'a.modal_' . $id);

JHtml::script('jui/fielduser.min.js', false, true, false, false, true);
?>
<?php // Create a dummy text field with the user name. ?>
<div class="input-append">
	<input
		type="text" id="<?php echo $id; ?>_name"
		value="<?php echo  htmlspecialchars($userName, ENT_COMPAT, 'UTF-8'); ?>"
		readonly
		disabled="disabled" <?php echo $size ? ' size="' . (int) $size . '"' : ''; ?> />
	<?php if (!$readOnly) : ?>
		<a class="btn btn-primary modal_<?php echo $id; ?>" title="<?php echo JText::_('JLIB_FORM_CHANGE_USER'); ?>" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
			<span class="icon-user"></span>
		</a>
	<?php endif; ?>
</div>

<?php // Create the real field, hidden, that stored the user id. ?>
<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo (int) $value; ?>" data-onchange="<?php echo $this->escape($onchange); ?>"/>
